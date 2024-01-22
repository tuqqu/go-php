<?php

declare(strict_types=1);

namespace GoPhp;

use GoPhp\Error\RuntimeError;

use function file_get_contents;
use function glob;
use function is_dir;
use function is_file;
use function sprintf;

class ImportHandler
{
    final protected const string EXTENSION_GO = '.go';
    final protected const string EXTENSION_GO_PHP = '.gop';

    protected const array EXTENSIONS = [
        self::EXTENSION_GO,
        self::EXTENSION_GO_PHP,
    ];

    private readonly array $extensions;

    public function __construct(
        private readonly EnvVarSet $envVars,
        array $customExtensions = [],
    ) {
        $this->extensions = self::EXTENSIONS + $customExtensions;
    }

    /**
     * @return iterable<string>
     */
    public function importFromPath(string $path): iterable
    {
        $path = sprintf('%s/src/%s', $this->envVars->goroot, $path);

        if (is_dir($path)) {
            yield from $this->importFromDirectory($path);

            return;
        }

        foreach ($this->extensions as $extension) {
            $file = $path . $extension;

            if (is_file($file)) {
                yield self::importFromFile($file);

                return;
            }
        }

        throw RuntimeError::cannotFindPackage($path);
    }

    /**
     * @return iterable<string>
     */
    protected function importFromDirectory(string $path): iterable
    {
        foreach ($this->extensions as $ext) {
            $files = glob(sprintf('%s/*%s', $path, $ext));

            foreach ($files as $file) {
                yield self::importFromFile($file);
            }

            // fixme recursive
            // fixme add _ . support
            // fixme add go.mod support
        }
    }

    protected static function importFromFile(string $path): string
    {
        return file_get_contents($path);
    }
}
