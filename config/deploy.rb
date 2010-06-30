set :application, 'home'
set :user, 'jparker'
set :use_sudo, false
set :deploy_to, '/home/jparker/_public_html'

set :repository, 'git@codaset.com:urgetopunt/home.git'
set :scm, :git
set :scm_verbose, true
set :deploy_via, :remote_cache
set :branch, 'master'

set :gateway, 'awooga@shell.speakeasy.net'

role :app, 'kiwi.urgetopunt.com'
role :web, 'kiwi.urgetopunt.com'
role :db,  'kiwi.urgetopunt.com', :primary => true

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
