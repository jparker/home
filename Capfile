load 'deploy'

set :use_sudo, false
set :deploy_to, "/home/jparker/site"
set :current_path, "/home/jparker/public_html"

set :repository, 'git@github.com:jparker/home.git'
set :scm, :git
set :branch, 'master'
# set :deploy_via, :remote_cache
set :deploy_via, :copy

role :web, 'papango.urgetopunt.com'
role :app, 'papango.urgetopunt.com'
role :db, 'papango.urgetopunt.com'
