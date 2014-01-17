set :application, 'cg_app'
set :deploy_to, "/var/www/#{fetch(:application)}"
set :keep_releases, 5

set :scm, :git
set :repo_url, 'git@bitbucket.org:channelgrabber/cg-app-service.git'
ask :branch, proc { `git rev-parse --abbrev-ref HEAD`.chomp }
set :deploy_via, :remote_cache

set :format, :pretty
set :log_level, :info

set :use_sudo, false
set :pty, true
set :ssh_options, {
    :forward_agent => true
}

set :linked_files, [
    "config/host.php",
    "config/autoload/cg_app.global.php",
    "config/autoload/database.local.php",
    "config/autoload/di.cgcache.global.php",
    "config/autoload/di.guzzle.global.php",
    "config/autoload/di.memcached.global.php",
    "config/autoload/di.mongo.global.php",
    "config/autoload/di.redis.global.php",
    "data/certificates/audit_client.pem",
    "data/certificates/cacert.pem",
    "data/certificates/cg_app_client.pem",
    "data/certificates/CGDirectoryApi_client.pem",
    "data/certificates/log_client.pem",
    "data/certificates/satis_client.pem",
    "data/di/php_internal-definition.php",
    "phinx/phinx.yml",
    "tests/api.suite.yml"
]
set :linked_dirs, []

namespace :deploy do
    desc 'Restart application'
    task :restart do
        on roles(:all), in: :parallel do
            execute :sudo, :restart, "php5-fpm"
        end
    end

    after :finishing, 'deploy:cleanup'
end
