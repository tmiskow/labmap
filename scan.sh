#!/bin/bash

exec 2> /dev/null

machines=("blue00" "blue01" "blue02" "blue03" "blue04" "blue05" "blue06" "blue07" "blue08" "blue09" "blue10" "blue11" "blue12" "blue13" "blue14" "blue15" "brown00" "brown01" "brown02" "brown03" "brown04" "brown05" "brown06" "brown07" "brown08" "brown09" "brown10" "brown11" "brown12" "brown13" "brown14" "brown15" "cyan00" "cyan01" "cyan02" "cyan03" "cyan04" "cyan05" "cyan06" "cyan07" "cyan08" "cyan09" "cyan10" "cyan11" "cyan12" "cyan13" "cyan14" "cyan15" "green00" "green01" "green02" "green03" "green04" "green05" "green06" "green07" "green08" "green09" "green10" "green11" "green12" "green13" "green14" "green15" "khaki01" "khaki02" "khaki03" "khaki04" "khaki05" "khaki06" "khaki07" "khaki08" "khaki09" "khaki10" "khaki11" "khaki12" "khaki13" "khaki14" "khaki15" "khaki16" "orange00" "orange01" "orange02" "orange03" "orange04" "orange05" "orange06" "orange07" "orange08" "orange09" "orange10" "orange11" "orange12" "orange13" "orange14" "orange15" "pink00" "pink01" "pink02" "pink03" "pink04" "pink05" "pink06" "pink07" "pink08" "pink09" "pink10" "pink11" "pink12" "pink13" "pink14" "pink15" "pink16" "pink17" "red00" "red01" "red02" "red03" "red04" "red05" "red06" "red07" "red08" "red09" "red10" "red11" "red12" "red13" "red14" "red15" "violet00" "violet01" "violet02" "violet03" "violet04" "violet05" "violet06" "violet07" "violet08" "violet09" "violet10" "violet11" "violet12" "violet13" "violet14" "violet15" "yellow00" "yellow01" "yellow02" "yellow03" "yellow04" "yellow05" "yellow06" "yellow07" "yellow08" "yellow09" "yellow10" "yellow11" "yellow12" "yellow13" "yellow14" "yellow15")

tempfile=$(mktemp)

ping_machine () {
	machine=$1
	cmd="ping -c 1 "$machine
	output=$($cmd)
	exit_code=$?
	ttl=$(echo $output | grep -o "ttl=[0-9]*" | grep -o "[0-9]*")
	echo $machine";"$exit_code";"$ttl
}

for machine in "${machines[@]}"
do
	ping_machine $machine &
done

wait

cat $tempfile
rm "$tempfile"
