<?php











namespace Composer;

use Composer\Semver\VersionParser;






class InstalledVersions
{
private static $installed = array (
  'root' => 
  array (
    'pretty_version' => '1.0.0+no-version-set',
    'version' => '1.0.0.0',
    'aliases' => 
    array (
    ),
    'reference' => NULL,
    'name' => '__root__',
  ),
  'versions' => 
  array (
    '__root__' => 
    array (
      'pretty_version' => '1.0.0+no-version-set',
      'version' => '1.0.0.0',
      'aliases' => 
      array (
      ),
      'reference' => NULL,
    ),
    'afragen/github-updater' => 
    array (
      'pretty_version' => 'dev-master',
      'version' => 'dev-master',
      'aliases' => 
      array (
      ),
      'reference' => '552a74fa0d3bd9b0b2ea9866201e0b473fa047cc',
    ),
    'afragen/singleton' => 
    array (
      'pretty_version' => 'dev-master',
      'version' => 'dev-master',
      'aliases' => 
      array (
        0 => '9999999-dev',
      ),
      'reference' => 'be8e3c3b3a53ba30db9f77f5b3bcf2d5e58ed9c0',
    ),
    'afragen/wordpress-plugin-readme-parser' => 
    array (
      'pretty_version' => 'dev-master',
      'version' => 'dev-master',
      'aliases' => 
      array (
        0 => '9999999-dev',
      ),
      'reference' => 'f878b2a815caace5f331b827b356b4eab9610561',
    ),
    'afragen/wp-dependency-installer' => 
    array (
      'pretty_version' => '4.3.4',
      'version' => '4.3.4.0',
      'aliases' => 
      array (
      ),
      'reference' => 'd13c7f2caab8781758c46ebef508953ce98259db',
    ),
    'afragen/wp-dismiss-notice' => 
    array (
      'pretty_version' => '0.2.4',
      'version' => '0.2.4.0',
      'aliases' => 
      array (
      ),
      'reference' => 'a5c05306a4c2f0b18aed7d9b27e97b247dc7773a',
    ),
    'erusev/parsedown' => 
    array (
      'pretty_version' => '1.7.4',
      'version' => '1.7.4.0',
      'aliases' => 
      array (
      ),
      'reference' => 'cb17b6477dfff935958ba01325f2e8a2bfa6dab3',
    ),
    'freemius/wordpress-sdk' => 
    array (
      'pretty_version' => '2.4.3',
      'version' => '2.4.3.0',
      'aliases' => 
      array (
      ),
      'reference' => 'd2c3e1c27792f34123f928ea9fa73be56c337d13',
    ),
  ),
);







public static function getInstalledPackages()
{
return array_keys(self::$installed['versions']);
}









public static function isInstalled($packageName)
{
return isset(self::$installed['versions'][$packageName]);
}














public static function satisfies(VersionParser $parser, $packageName, $constraint)
{
$constraint = $parser->parseConstraints($constraint);
$provided = $parser->parseConstraints(self::getVersionRanges($packageName));

return $provided->matches($constraint);
}










public static function getVersionRanges($packageName)
{
if (!isset(self::$installed['versions'][$packageName])) {
throw new \OutOfBoundsException('Package "' . $packageName . '" is not installed');
}

$ranges = array();
if (isset(self::$installed['versions'][$packageName]['pretty_version'])) {
$ranges[] = self::$installed['versions'][$packageName]['pretty_version'];
}
if (array_key_exists('aliases', self::$installed['versions'][$packageName])) {
$ranges = array_merge($ranges, self::$installed['versions'][$packageName]['aliases']);
}
if (array_key_exists('replaced', self::$installed['versions'][$packageName])) {
$ranges = array_merge($ranges, self::$installed['versions'][$packageName]['replaced']);
}
if (array_key_exists('provided', self::$installed['versions'][$packageName])) {
$ranges = array_merge($ranges, self::$installed['versions'][$packageName]['provided']);
}

return implode(' || ', $ranges);
}





public static function getVersion($packageName)
{
if (!isset(self::$installed['versions'][$packageName])) {
throw new \OutOfBoundsException('Package "' . $packageName . '" is not installed');
}

if (!isset(self::$installed['versions'][$packageName]['version'])) {
return null;
}

return self::$installed['versions'][$packageName]['version'];
}





public static function getPrettyVersion($packageName)
{
if (!isset(self::$installed['versions'][$packageName])) {
throw new \OutOfBoundsException('Package "' . $packageName . '" is not installed');
}

if (!isset(self::$installed['versions'][$packageName]['pretty_version'])) {
return null;
}

return self::$installed['versions'][$packageName]['pretty_version'];
}





public static function getReference($packageName)
{
if (!isset(self::$installed['versions'][$packageName])) {
throw new \OutOfBoundsException('Package "' . $packageName . '" is not installed');
}

if (!isset(self::$installed['versions'][$packageName]['reference'])) {
return null;
}

return self::$installed['versions'][$packageName]['reference'];
}





public static function getRootPackage()
{
return self::$installed['root'];
}







public static function getRawData()
{
return self::$installed;
}



















public static function reload($data)
{
self::$installed = $data;
}
}
