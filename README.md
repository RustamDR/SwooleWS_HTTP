Swoole Server testing


#### Installation
~~~
Before install:
1. Check your github token
2. Copy .env.example to .env (add github token to GIT_TOKEN) and docker-compose.example.yml to docker-compose.yml
3. Setup SERVER listen IPs and port
4. Copy app/config/config.example.php to app/config/config.php  
~~~

Docker install:
~~~
sudo ./bin/docker-install
~~~
or
~~~
https://docs.docker.com/engine/installation/
~~~

Check after install:
~~~
sudo docker run hello-world
~~~

Without sudo run
~~~
sudo groupadd docker
~~~
~~~
sudo usermod -aG docker $USER
~~~

If you get
~~~
WARNING: Error loading config file: /home/user/.docker/config.json -
stat /home/user/.docker/config.json: permission denied
~~~
то
~~~
sudo chown "$USER":"$USER" /home/"$USER"/.docker -R
sudo chmod g+rwx "/home/$USER/.docker" -R
~~~

Run Server
~~~
./bin/start
~~~

Stop server
~~~
./bin/stop
~~~