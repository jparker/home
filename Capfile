load 'deploy'

set :use_sudo, false
set :deploy_to, "/home/jparker/site"
set :current_path, "/home/jparker/public_html"

set :repository, 'git@github.com:jparker/home.git'
set :scm, :git
set :branch, 'master'
set :git_shallow_clone, 1

role :web, 'papango.urgetopunt.com'
