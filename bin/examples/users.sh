#!/bin/zsh

BASEDIR=${0:a:h}
# The file is in BASEDIR/../jsondbserver.php
PHP=${BASEDIR}/../jsondbserver.php

# Create a new user with username "user123" and password "password123"
PHP ${PHP} insert 'example_users' '{"username":"user123","password":"password123"}'

# Create another user with username "user456" and password "password456"
PHP ${PHP} insert 'example_users' '{"username":"user456","password":"password456"}'

# Update the password to "betterpassword123" for the user "user123"
PHP ${PHP} update 'example_users' 'username' 'user123' '{"password":"betterpassword123"}'

# Select all users
PHP ${PHP} select 'example_users'
print #Newline

# Select all users with username "user123"
PHP ${PHP} select 'example_users' '{"username":"user123"}'
print #Newline

# Create another user with username "differentname" and password "differentpassword"
PHP ${PHP} insert 'example_users' '{"username":"differentname","password":"differentpassword"}'

# Select all users with username starting with "user" ('user%')
PHP ${PHP} select 'example_users' '{"username":"user%"}' 'true'
print #Newline

# Delete the user with username "user123"
PHP ${PHP} delete 'example_users' 'username' 'user123'

# Last step,
# Delete the table
PHP ${PHP} deletetable 'example_users'
