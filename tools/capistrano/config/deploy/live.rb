set :stage, :live

server 'www-data@82.145.48.2', :roles => [:app, :php71]
server 'www-data@109.169.46.68', :roles => [:app, :php71]
server 'www-data@109.169.50.137', :roles => [:app, :php5]
