# ZabbixNexis

Simple project based on Zabbix for my internship at Nexis

The goal of this project is to make a multi-client Zabbix with one Master, and transfer the alerts and specific datas through HTTP.
For this purpose, I've to create some scripts for the client and for the supervisor.

I choosed PHP because it's all the functions I want to use. I did that for my internship and I'm not a developer, so don't blame my messy bunch of cra... hum code !

# Author

Massinon Antoine

# Library used

I used the PHPZabbixAPI from https://github.com/confirm/PhpZabbixApi, it does all I want and it's simple to use

# Usage

 If you want to use my scripts, you've to : 
- Edit the user/password here : $api = new ZabbixApi('http://10.254.0.10/api_jsonrpc.php', 'YourUser', 'YourPassword');
- Create manually (at the moment) the file "data.txt" and gave it 755 rights
Simply call once the collect.php script, then it's over you can retrieve your data in the data.txt and the output php page "affichage.php"
