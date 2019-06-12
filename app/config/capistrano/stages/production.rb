set :stage, :production
set :domain, 'home655277423.1and1-data.host'

server fetch(:domain), user: 'u87073604', roles: %w{web app db}

set :deploy_to, '/kunden/homepages/39/d655277423/htdocs/numerology'
set :branch, 'master'

set :composer_install_flags, '--no-dev --prefer-dist --no-interaction --quiet --optimize-autoloader --ignore-platform-reqs'

SSHKit.config.command_map[:composer] = "#{fetch(:deploy_to)}/shared/composer.phar"
SSHKit.config.command_map[:composer] = "#{shared_path.join("composer.phar")}"
SSHKit.config.command_map[:php] = "php7.1-cli"
