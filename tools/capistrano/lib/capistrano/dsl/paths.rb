module Capistrano
    module DSL
        module Paths
            def shared_path
                version = JSON.parse(IO.read('../../config/autoload/version.json'))
                paths = ['shared']

                if version.is_a?(Hash) && version.has_key?('version')
                    paths.unshift('version', "#{version['version']}")
                end

                deploy_path.join(*paths)
            end
        end
    end
end