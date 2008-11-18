set :application, 'home'
set :user, 'jparker'
set :use_sudo, false
set :deploy_to, '/home/jparker/.public_html'

set :repository, 'git@github.com:jparker/home.git'
set :scm, :git
set :scm_verbose, true
set :deploy_via, :remote_cache
set :branch, 'master'

set :gateway, 'shell.speakeasy.net'

role :app, 'papango.urgetopunt.com'
role :web, 'papango.urgetopunt.com'
role :db,  'papango.urgetopunt.com', :primary => true

namespace :deploy do
  task :finalize_update do
    # [no-op] overwrite default Rails-centric task
  end

  task :migrate do
    # [no-op] overwrite default Rails-centric task
  end

  task :restart do
    # [no-op] overwrite default Rails-centric task
  end
end
