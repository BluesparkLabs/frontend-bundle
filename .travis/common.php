<?php

function shouldBuildDocs()
{
    return isLatestPhp() && isLatestSymfony();
}

function withCodeCoverage()
{
    return isLatestPhp() && in_array(getSymfonyVersion(), array('2.3.*', '3.2.*'));
}

function isHhvm()
{
    return getPhpVersion() === 'hhvm';
}

function isLatestPhp()
{
    return getPhpVersion() === '7.1';
}

function isLatestSymfony()
{
    return getSymfonyVersion() === '3.2.*';
}

function getSymfonyVersion()
{
    return getenv('SYMFONY_VERSION');
}

function getPhpVersion()
{
    return exec('phpenv version-name');
}

function runCommand($command)
{
    echo "$ $command\n";

    passthru($command, $ret);

    if ($ret !== 0) {
        exit($ret);
    }
}
