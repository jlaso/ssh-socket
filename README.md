# SSH socket

![screenshot](https://github.com/jlaso/ssh-socket/raw/master/doc/screenshot.png "Sample of work")

## requirements

LibSSH2 on system, and ssh2 php module

## config

Configure your ssh credentials and port in src/config/parameters.ini

```
[config]
user=vagrant
password=vagrant
port=8081
```

## testing

### in your own machine

Remember to enable WebSharing if you are using a MAC OSx

you can test this component with the demo included, 

firstly, launching the server:

```
php src/server.php
```

and in another tab launching the embeded server

```
php -S localhost 8080
```

Now in a compatible HTML5  browser go to http://localhost:8080 and play with the demo.

### with the vagrant machine included

move to the vagrant folder and start machine

```
cd vagrant
vagrant up
```

get some coffee while the machine is provisioned

enter into the machine and start the dispatcher of sockets

```
vagrant ssh
cd /vagrant
php src/server.php
```

And now you can go to http://10.10.10.18 in a browser to test the demo

## version

1.0
