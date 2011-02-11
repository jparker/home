set :application, 'home'
set :user, 'jparker'
set :use_sudo, false
set :deploy_to, '/home/jparker/_public_html'

set :repository, 'git@github.com:jparker/home.git'
set :scm, :git
set :scm_verbose, true
set :deploy_via, :remote_cache
set :branch, 'master'

if ENV['CAP_SSH_GATEWAY']
  set :gateway, ENV['CAP_SSH_GATEWAY']
end

role :app, 'ash.urgetopunt.com'
role :web, 'ash.urgetopunt.com'
role :db,  'ash.urgetopunt.com', :primary => true

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
