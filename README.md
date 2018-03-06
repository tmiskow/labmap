# labmap

http://students.mimuw.edu.pl/~tm385898/labmap/

Web application that shows current data about the usage of computers in MIM UW laboratories. It shows how many computers are occupied and in which rooms there are active courses at the moment.

It allows students to find a perfect spot for individual studying in between classes or an active machine to use as a remote host when working from home.

## Graphical interface

![](https://i.imgur.com/86bBRU6.png)

## API interface
#### URL structure: 
`students.mimuw.edu.pl/~tm385898/labmap/api/rooms/{room_number}/computers/{computer_index}`
#### Example:
`students.mimuw.edu.pl/~tm385898/labmap/api/rooms/2041/computers/3`

```javascript
{
  code: 200,
  message: "Success.",
  data: {
    name: "red03",
    state: "linux",
    user: {
      login: "ab123456",
      name: "Jan",
      surname: "Przykładowy"
    }
  }
}
```


## Database model
![](https://i.imgur.com/5JHlTDF.png)

## File descriptions

### scan.sh

Bash script that checks which computers are turned off and what OS they're running.

### update.py

The core of Labmap - a python script run in five minutes intervals that:

1. runs `scan.sh` to gather info about the state of all machines.
2. connects via ssh with all Linux hosts using Fabric and checks which have a user currently logged in.
3. converts its findings into SQL and pushes the data into Oracle databse.

### labmap.sql

Defintion of the database tables and initial rooms data.

### scripts.sql

SQL views used in extraction of data about current courses.

### index.php

PHP file that extracts data from database and generates webpage presenting it.

### index.css

Stylesheet for `index.php`.

### api.php

PHP file that analyzes API request based on URL and generates response with functions defined in `data.php`.

### data.php

Functions defintions for extracting data from database and forming JSON reponses.

