# -*- coding: utf-8 -*-

import os
import csv
import re
import subprocess
from fabric.api import env, hide, task, execute, run
from fabric.exceptions import NetworkError
from datetime import datetime
import secret
from time import sleep

# to ensure proper encoding when inserting data into the database
os.environ["NLS_LANG"] = "POLISH_POLAND.EE8ISO8859P2"

class FabricException(Exception):
	def __init__(self, s):
		self.s = s

	def __str__(self):
		return self.s

# fabric env settings
env.user = secret.user
env.password = secret.password
env.no_keys = True
env.no_agent = True
env.skip_bad_hosts = True
env.parallel = False
env.abort_exception = FabricException
env.abort_on_prompts = True

users = []
computers = []

room_dict = {
	'red': 2041,
	'pink': 2042,
	'orange': 2043,
	'brown': 2044,
	'yellow': 2045,
	'khaki': 3041,
	'green': 3042,
	'cyan': 3043,
	'blue': 3044,
	'violet': 3045
}

class User():
	def __init__(self, login, name, surname):
		self.login = login
		self.name = name
		self.surname = surname

	def __str__(self):
		return str((self.login, self.name, self.surname))

	def get_insert_str(self):
		string = 'INSERT INTO Users\n'
		string += '\t(login, name, surname)\n'
		string += 'VALUES\n'
		string += '\t' + str(self) + ';\n'

		return string

class Computer():
	def __init__(self, computer_id, name, exit_code, ttl):
		if exit_code == 0:
			if ttl == 64:
				state = 'linux'
			else:
				state = 'windows'
		else:
			state = 'off'

		match = re.match('([^\d\s]+)(\d+)', name)
		color = match.group(1)
		index = match.group(2)
		room_ref = room_dict[color]

		self.name = name
		self.computer_id = computer_id
		self.room_ref = room_ref
		self.index = index
		self.state = state
		self.user_ref = None

	def __str__(self):
		if self.user_ref == None:
			return '(' + str(self.computer_id) + ', ' + str(self.room_ref) + ", '" + self.index + "', '" + self.state + "', NULL)"
		return str((self.computer_id, self.room_ref, self.index, self.state, self.user_ref))

	def get_insert_str(self):
		string = 'INSERT INTO Computers\n'
		string += '\t(computer_id, room_ref, "index", state, user_ref)\n'
		string += 'VALUES\n'
		string += '\t' + str(self) + ';\n'

		return string

def get_user(searched_login):
	for user in users:
		if user.login == searched_login:
			return user
		else:
			return None

def get_computer(searched_name):
	for computer in computers:
		if searched_name == computer.name:
			return computer

def add_user(login, name, surname, computer):
	user = get_user(login)

	if user is None:
		user = User(login, name, surname)
		users.append(user)

	computer.user_ref = login

	return user

@task
def print_user():
	with hide('warnings', 'running', 'stdout', 'stderr'):
		output = run("finger")
		user_lines = str(output).split('\n')[4:]

		unmatched_lines = []

		for line in user_lines:
			regex = '(\w+)\s+([^\s]+) ([^\s]+)+\s+([\w/]+\d)[^\n]*'
			match = re.match(regex, line)
			if match is None:
				if not re.match('Login\s+Name\s+Tty\s+Idle\s+Login\s+Time\s+Office\s+Office\s+Phone\s+', line):
					unmatched_lines.append(line)
			else:
				login = match.group(1)
				name = match.group(2)
				surname = match.group(3)
				tty = match.group(4).startswith('tty')

				if tty == True:
					return ((login, name, surname), unmatched_lines)
		return (None, unmatched_lines)


# SCRIPT
with open('machines.log', 'a') as log:
	users = []
	computers = []
	start_time = datetime.now()
	log.write(start_time.strftime('%Y-%m-%d %H:%M:%S') + '\n')

	# generate machines ping scan results
	with open('machines.csv', 'w') as machines_csv:
	    subprocess.run("./scan.sh", stdout=machines_csv)

	# load machines ping scan results
	with open('machines.csv', 'r') as machines_csv:
		reader = csv.reader(machines_csv, delimiter=';')
		for i, (machine, exit_code, ttl) in enumerate(reader):
			if ttl == '':
				ttl = 0
			else:
				ttl = int(ttl)

			computers.append(Computer(i, machine, int(exit_code), int(ttl)))

	# get hosts able to make a ssh connection
	hosts = [computer.name for computer in computers if computer.state == 'linux']

	log.write('active linux hosts: ' + str(len(hosts)) + '\n')

	if hosts != []:
		# get additional data about ssh-able machines
		with hide('running', 'warnings'):

			try:
				result = execute(print_user, hosts=hosts)
			except FabricException as e:
				log.write(str(e) + '\n')

			for host in result:
				computer = get_computer(host)
				if isinstance(result[host], NetworkError):
					computer.state = 'macos'
				else:
					result_tuple, unmatched_lines = result[host]

					if result_tuple is not None:
						login, name, surname = result_tuple
						user = add_user(login, name, surname, computer)

					for unmatched_line in unmatched_lines:
						log.write(host + ': ' + unmatched_line + '\n')

	subprocess.run(['cp', 'labmap.sql', 'machines.sql'])

	# create sql script
	with open('machines.sql', 'a') as machines_sql:
		for user in users:
			machines_sql.write(user.get_insert_str())

		for computer in computers:
			machines_sql.write(computer.get_insert_str())

		machines_sql.write("INSERT INTO Updates VALUES (to_date('" + str(datetime.now()).split('.')[0] + "', 'YYYY-MM-DD HH24:MI:SS'));\n")

	with open(os.devnull, 'w')  as devnull:
		p1 = subprocess.Popen(['echo', 'exit'], stdout=subprocess.PIPE)
		p2 = subprocess.Popen(['sqlplus', secret.user + '/' + secret.password + '@labs', '@machines.sql'], stdin=p1.stdout, stdout=devnull)

		p1.communicate()
		p2.wait()

	end_time = datetime.now()
	log.write(end_time.strftime('%Y-%m-%d %H:%M:%S') + '\n')

	elapsed = end_time - start_time
	log.write('DONE in ' + str(elapsed.seconds) + ' seconds\n\n')

