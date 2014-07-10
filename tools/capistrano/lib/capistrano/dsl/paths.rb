module Capistrano
    module DSL
        module Paths
            def shared_path
                version = fetch(:version)
                paths = ['shared']

                if version
                    paths.unshift('version', "#{version}")
                end

                deploy_path.join(*paths)
            end
        end
    end
end