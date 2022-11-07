# jsondbserver

## What is JSONDBSERVER?

JSONDBServer is a simple, fast, and lightweight JSON database server. 
It is written in PHP and writes into table files in JSON format.
It can be used as a simple database server for small projects.

It has support for complicated select statements (where, and/or, etc), aswell as pagination and sorting.
Select statement also have LIKE support, such as "%john%" or "john%" or "%john".

You can use the jsondbserver.php as a cli tool to execute commands or the JsonDB class to use it in your own php project.

## Installation

```git clone https://github.com/TimAnthonyAlexander/jsondbserver ~/jsondbserver```

```~/jsondbserver/bin/install.sh```

[![Installation](https://i.ibb.co/YchmJ7M/Screenshot-2022-11-07-at-3-23-06-PM.png)]()

## Usage

```jsondbserver help```

[![Installation](https://i.ibb.co/n3NGRy4/Screenshot-2022-11-07-at-3-26-17-PM.png)]()

```jsondbserver <command> ...<arguments>```


JSON arguments expect valid JSON with escaped single-quotes. For example:

```jsondbserver insert 'users' '{"key":"value with \'escaped single-quotes\'"}'```


Select statements return JSON. 
Please view bin/examples/users.sh for a functioning example.

## Examples

### Create a new user with username "user123" and password "password123"
```jsondbserver insert 'example_users' '{"username":"user123","password":"password123"}'```

### Create another user with username "user456" and password "password456"
```jsondbserver insert 'example_users' '{"username":"user456","password":"password456"}'```

### Update the password to "password123456789" for the user "user123"
```jsondbserver update 'example_users' 'username' 'user123' '{"password":"password123456789"}'```

### Select all users
```jsondbserver select 'example_users'```

### Select all users with username "user123"
```jsondbserver select 'example_users' '{"username":"user123"}'```

### Select all users with username starting with "user" ('user%')
```jsondbserver select 'example_users' '{"username":"user%"}' 'true'```
