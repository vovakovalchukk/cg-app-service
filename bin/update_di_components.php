<?php
define('YELLOW', "\033[33m");
define('GREEN', "\033[32m");
define('RED', "\033[31m");
define('WHITE', "\033[0m");
define('COMPONENTS_FILE', 'config/di/vendor_components.php');

if (!is_writable(COMPONENTS_FILE)) {
    echo RED . 'Cannot update DI components as ' . COMPONENTS_FILE . ' is not read/writeable. Check the file permissions or try sudo-ing' . WHITE . PHP_EOL;
    exit(1);
}

require_once 'bootstrap.php';

echo 'Updating DI components' . PHP_EOL;

$vendorExclusions = ['bin', 'composer'];
$projectExclusions = [];
$vendorDir = 'vendor/';
$currentComponents = require COMPONENTS_FILE;
$newComponents = [];

$vendors = new DirectoryIterator($vendorDir);
foreach ($vendors as $vendor) {
    if ($vendor->isDot() || !$vendor->isDir() || in_array($vendor->getFilename(), $vendorExclusions)) {
        continue;
    }
    $vendorName = $vendor->getFilename();

    $projects = new DirectoryIterator($vendor->getPathname());
    foreach ($projects as $project) {
        if ($project->isDot() || !$project->isDir() || in_array($project->getFilename(), $projectExclusions)) {
            continue;
        }
        $projectName = $project->getFilename();

        $componentNameParts[] = $projectName;
        $srcDirParts = getSrcDirParts($project->getPathname(), $vendorName, $projectName);
        if ($srcDirParts === false) {
            echo YELLOW . 'WARNING: ' . WHITE . 'could not determine source/library directory for ' . $vendorName
                . ' ' . $projectName . ', skipping' . PHP_EOL;
            continue;
        }
        $srcDir = $srcDirParts['srcDir'];
        if ($srcDir == '.') {
            $srcDir = '';
        }
        $srcDirPath = $project->getPathname() . '/' . $srcDir;

        if (isset($srcDirParts['namespaceDir'])) {
            $namespaceDir = $srcDirParts['namespaceDir'];
        } else {
            $namespaceDir = determineNamespaceDir($srcDirPath, $vendorName, $projectName);
            if ($namespaceDir === false) {
                echo YELLOW . 'WARNING: ' . WHITE . 'could not determine namespaced directory for ' . $vendorName . ' '
                    . $projectName . ', skipping' . PHP_EOL;
                continue;
            }
        }

        $componentNameParts = array_merge([$vendorName, $projectName], explode('/', $namespaceDir));
        $componentName = implode('_', $componentNameParts);
        echo $componentName . '... ';
        $newComponents[] = $componentName;

        if (in_array($componentName, $currentComponents)) {
            echo 'exists' . PHP_EOL;
            continue;
        }
        echo GREEN . 'added' . WHITE . PHP_EOL;
    }
}

saveComponents($newComponents);

echo 'DONE' . PHP_EOL;
exit(0);

function getSrcDirParts($path, $vendor, $project)
{
    $srcDirOptions = ['', 'src', 'library', 'lib'];
    if (file_exists($path . '/composer.json')) {
        $composerJson = json_decode(file_get_contents($path . '/composer.json'));
        if (isset($composerJson->autoload->{'psr-0'})) {
            foreach ($srcDirOptions as $srcDirOption) {
                if (in_array($srcDirOption, (array)$composerJson->autoload->{'psr-0'})) {
                    $ret = ['srcDir' => $srcDirOption];
                    $namespace = array_search($srcDirOption, (array)$composerJson->autoload->{'psr-0'});
                    if ($namespace != '') {
echo '*** '.$namespace.'***'.PHP_EOL;
                        $ret['namespaceDir'] = preg_replace('|\\+|', '/', $namespace);
echo '*** '.$ret['namespaceDir'].'***'.PHP_EOL;
                    }
                    return $ret;
                }
            }

            $ucVendor = ucwords($vendor);
            $ucProject = ucwords($project);
            if (isset($composerJson->autoload->{'psr-0'}->$ucVendor)) {
                return ['srcDir' => $composerJson->autoload->{'psr-0'}->$ucVendor];
            } elseif (isset($composerJson->autoload->{'psr-0'}->$ucProject)) {
                return ['srcDir' => $composerJson->autoload->{'psr-0'}->$ucProject];
            }
        }
    }

    // Haven't returned yet, try a few more options
    foreach ($srcDirOptions as $srcDirOption) {
        if ($srcDirOption == '') {
            continue;
        }
        if (is_dir($path . '/' . $srcDirOption)) {
            return ['srcDir' => $srcDirOption];
        }
    }

    return false;
}

function determineNamespaceDir($path, $vendor, $project)
{
if ($vendor == 'psr') {
    echo __FUNCTION__."($path, $vendor, $project)\n";
}
    $vendor = ucwords($vendor);
    $project = ucwords($project);
    $vendorConversions = [
        'Channelgrabber' => 'CG',
        'Phpunit' => 'PHP'
    ];
    $projectConversions = [];
    if (in_array($vendor, $vendorConversions)) {
        $vendor = $vendorConversions[$vendor];
    }
    if (in_array($project, $projectConversions)) {
        $project = $projectConversions[$project];
    }

    if (is_dir($path . '/' . $vendor . '/' . $project)) {
        return $vendor . '/' . $project;
    } else if (is_dir($path . '/' . $vendor)) {
        return $vendor;
    } else if (is_dir($path . '/' . $project)) {
        return $project;
    }

    return false;
}

function saveComponents($components)
{
    sort($components);
    $output = '<?php' . PHP_EOL
        . '/*' . PHP_EOL . ' * This file is generated but should still be committed' . PHP_EOL
        . ' */' . PHP_EOL
        . 'return array(' . PHP_EOL;
    foreach ($components as $component) {
        $output .= '    \'' . $component . '\',' . PHP_EOL;
    }
    $output .= ');';

    file_put_contents(COMPONENTS_FILE, $output);
}