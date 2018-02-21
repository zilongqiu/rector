<?php declare(strict_types=1);

namespace Rector\PharBuilder;

use FilesystemIterator;
use Phar;
use Seld\PharUtils\Timestamps;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * 1st step: make hardcoded work
 * 2nd step: make abstract work - use Configuration class to provide all the stuffs
 */
class Compiler
{
    public function compile(string $buildDir): void
    {
        ini_set('phar.readonly', '0');

        // flags: KEY_AS_PATHNAME - use relative paths from Finder keys
        $phar = new Phar('rector.phar', FilesystemIterator::KEY_AS_PATHNAME, 'rector.phar');
        $phar->setSignatureAlgorithm(Phar::SHA1);
        $phar->startBuffering();

        $finder = $this->createFinderWithAllFiles($buildDir);
        $phar->buildFromIterator($finder->getIterator(), $buildDir);

        $this->addRectorBin($phar);
        $phar->compress(Phar::GZ);

        $phar->setStub($this->getStub());
        $phar->stopBuffering();


        $timestamps = new Timestamps('rector.phar');
        $timestamps->save('rector.phar', Phar::SHA1);
    }

    private function addRectorBin(Phar $phar): void
    {
        $content = file_get_contents(__DIR__ . '/../../../bin/rector');
        // remove shebang from bin, causes errors
        $content = preg_replace('~^#!/usr/bin/env php\s*~', '', $content);
        // replace relative paths by phar paths
        $content = preg_replace("~__DIR__\\s*\\.\\s*'\\/\\.\\.\\/~", "'phar://rector.phar/", $content);

        $phar->addFromString('bin/rector', $content);
    }

    private function getStub(): string
    {
        return <<<'EOF'
#!/usr/bin/env php
<?php
Phar::mapPhar('rector.phar');
require 'phar://rector.phar/bin/rector';
__HALT_COMPILER();
EOF;
    }

    private function createFinderWithAllFiles(string $buildDir): Finder
    {
        // what is this for?
        $finderSort = function (SplFileInfo $a, SplFileInfo $b) {
            return strcmp(
                strtr($a->getRealPath(), '\\', '/'),
                strtr($b->getRealPath(), '\\', '/')
            );
        };

        return (new Finder())
            ->files()
            ->ignoreVCS(true)
            ->name('*.{yml,php}')
            ->in([
                $buildDir . '/src',
                $buildDir . '/packages',
                $buildDir . '/vendor'
            ])
            ->exclude(['tests', 'docs', 'Tests', 'phpunit'])
            ->sort($finderSort);
//            ->append(['bin/rector_bootstrap.php']);
    }
}