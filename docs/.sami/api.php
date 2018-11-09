<?php

use Sami\Sami;
use Sami\RemoteRepository\GitHubRemoteRepository;
use Sami\Version\GitVersionCollection;
Use Symfony\Component\Finder\Finder;

$dir = __DIR__.'/../../shortlist';

$iterator = Finder::create()
	->files()
	->name('*.php')
	->exclude('migrations')
	->exclude('resources')
	->exclude('templates')
	->in($dir)
;

$versions = GitVersionCollection::create($dir)
	->add('feature/docs', 'Version 1.x')
;

$sami = new Sami($iterator, array(
	'theme'                => 'topshelfcraft',
	'versions'             => $versions,
	'title'                => 'Shortlist Class Reference',
	'build_dir'            => __DIR__.'/dist/%version%',
	'cache_dir'            => __DIR__.'/cache/%version%',
	'remote_repository'    => new GitHubRemoteRepository('TopShelfCraft/Shortlist', dirname($dir)),
	'default_opened_level' => 2,
));

$templates = $sami['template_dirs'];
$templates[] = __DIR__.'/themes';

$sami['template_dirs'] = $templates;

return $sami;