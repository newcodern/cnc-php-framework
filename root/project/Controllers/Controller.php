<?php

namespace Controllers;

use Contingent\{
    DB, Kanbayashi_Mao, Sakakino_Konomi, SessionManager, PasswordEncryptor, D_AyaNe, D_SeEm, D_TeHa, KomponenFungsiAset,
    EncryptionManager, CsrfProtection, TimestampHandler, DeviceFingerprint, GenerateRandomUserName,
    PasswordGenerator, ImgBBUploader, Linker, Captha, Pusat_Komunikasi
};

use Utopia\KoSu;

Pusat_Komunikasi::register();

class Controller
{
    protected static array $globalVar = [];
    public static array $directives = [];
    protected static string $viewsBaseDir;
    protected static string $templateCacheDir;
    protected static array $services = [];
    protected static CsrfProtection $csrf;

    public function __construct()
    {
        self::$viewsBaseDir = $this->detectViewsBaseDir();
        self::$templateCacheDir = __DIR__ . '/../Cache/Templates';

        if (!is_dir(self::$templateCacheDir)) {
            mkdir(self::$templateCacheDir, 0777, true);
        }

        self::initializeServices();
        self::registerDirectives();
        self::$csrf->checkToken();
    }

    protected static function initializeServices(): void
    {
        self::$services = [
            'db' => DB::connection(),
            'kanbayashiMao' => new Kanbayashi_Mao(),
            'sakakinoKonomi' => new Sakakino_Konomi(),
            'sessionManager' => new SessionManager(),
            'passwordEncryptor' => new PasswordEncryptor(),
            'D_AyaNe' => new D_AyaNe(),
            'D_SeEm' => new D_SeEm(),
            'D_TeHa' => new D_TeHa(),
            'komponenFungsiAset' => new KomponenFungsiAset(),
            'EnMa' => new EncryptionManager(),
            'csrf' => new CsrfProtection(),
            'TH' => new TimestampHandler(),
            'DF' => new DeviceFingerprint(),
            'GRUN' => new GenerateRandomUserName(),
            'PaGe' => new PasswordGenerator(),
            'Linker' => new Linker(),
            'Captha' => new Captha(),
            'directives' => self::$directives
        ];

        class_alias(KomponenFungsiAset::class, 'Asset');
        class_alias(D_TeHa::class, 'D_TeHa');
        class_alias(D_SeEm::class, 'D_SeEm');
        class_alias(D_AyaNe::class, 'D_AyaNe');
        class_alias(db::class, 'db');
        class_alias(SessionManager::class, 'SessionManager');
        class_alias(PasswordEncryptor::class, 'PasswordEncryptor');
        class_alias(EncryptionManager::class, 'EncryptionManager');
        class_alias(TimestampHandler::class, 'TH');
        class_alias(DeviceFingerprint::class, 'DeviceFingerprint');
        class_alias(GenerateRandomUserName::class, 'GenerateRandomUserName');
        class_alias(PasswordGenerator::class, 'PasswordGenerator');
        class_alias(Linker::class, 'Linker');
        class_alias(Captha::class, 'Captha');
        class_alias(ImgBBUploader::class, 'ImgBBUploader');

        self::$csrf = self::$services['csrf'];
    }

    protected static function detectViewsBaseDir(): string
    {
        return __DIR__ . '/../view';
    }

    public static function renderView(string $viewName, ...$data): void
{
    $viewPath = str_replace('.', '/', $viewName);
    $viewFile = self::$viewsBaseDir . "/{$viewPath}.sora.php";

    if (file_exists($viewFile)) {
        $cacheFile = self::$templateCacheDir . '/' . md5($viewFile) . '.php';

        if (!self::isCacheValid($viewFile, $cacheFile)) {
            $compiledContent = self::compileTemplate(file_get_contents($viewFile), $viewFile);
            file_put_contents($cacheFile, $compiledContent);
        }

        $data = array_merge(...$data);
        extract($data, EXTR_SKIP);
        ob_start();
        include $cacheFile;
        $content = ob_get_clean();
        echo self::processDirectives($content, $viewFile);
    } else {
        self::handleNotFound();
    }
}

    protected static function isCacheValid(string $viewFile, string $cacheFile): bool
    {
        if (!file_exists($cacheFile) || filemtime($viewFile) > filemtime($cacheFile)) {
            return false;
        }

        foreach (self::getIncludedFiles($viewFile) as $includedFile) {
            if (file_exists($includedFile) && filemtime($includedFile) > filemtime($cacheFile)) {
                return false;
            }
        }

        return true;
    }

    protected static function getIncludedFiles(string $viewFile): array
    {
        $includedFiles = [];
        preg_match_all('/@include\((.*?)\)/', file_get_contents($viewFile), $matches);

        foreach ($matches[1] as $match) {
            $includeFile = self::$viewsBaseDir . '/' . trim($match, " '") . '.sora.php';
            if (file_exists($includeFile)) {
                $includedFiles[] = $includeFile;
                // Recursively get included files
                $includedFiles = array_merge($includedFiles, self::getIncludedFiles($includeFile));
            }
        }

        return $includedFiles;
    }

    protected static function compileTemplate(string $templateContent, string $viewFile): string
    {
        return self::processDirectives($templateContent, $viewFile);
    }

    public static function processDirectives(string $content, string $viewFile): string
    {
        foreach (self::$directives as $directiveName => $directive) {
            try {
                $content = $directive->process($content);
            } catch (\Exception $e) {
                $message = sprintf(
                    "[Directive] => %s , [error] => '%s' , [View_File] => %s , [line] => %d , [errorCode] => %d",
                    $directiveName, $e->getMessage(), $viewFile, $e->getLine(), 902
                );
                throw new \Exception($message, $e->getCode(), $e);
            }
        }

        return $content;
    }

    protected static function registerDirectives(): void
    {
        self::$directives = [
            'include' => new IncludeDirective(self::$services, self::$viewsBaseDir, self::$templateCacheDir),
            'include_once' => new IncludeOnceDirective(self::$services, self::$viewsBaseDir, self::$templateCacheDir),
            'require' => new RequireDirective(self::$services, self::$viewsBaseDir, self::$templateCacheDir),
            'require_once' => new RequireOnceDirective(self::$services, self::$viewsBaseDir, self::$templateCacheDir),
            'foreach' => new ForeachDirective(),
            'EndForeachDirective' => new EndForeachDirective(),
            'if' => new IfDirective(),
            'else' => new ElseDirective(),
            'elseif' => new ElseIfDirective(),
            'endif' => new EndIfDirective(),
            'echo' => new EchoDirective(),
            'php' => new PhpDirective(),
            'flash' => new FlashDirective(),
            'for' => new ForDirective(),
            'endfor' => new EndforDirective(),
            'csrf' => new CsrfDirective(self::$services['csrf']),
            'captcha' => new CaptchaDirective(self::$services['Captha'])
        ];
    }

    protected static function handleNotFound(): void
    {
        include_once('../../404.php');
        exit();
    }

    // Getter methods for services
    public static function db(): KoSu
    {
        return self::$services['db'];
    }

    public static function sessionManager(): SessionManager
    {
        return self::$services['sessionManager'];
    }
    
    public static function Captha(): Captha
    {
        return self::$services['Captha'];
    }

    public static function passwordEncryptor(): PasswordEncryptor
    {
        return self::$services['passwordEncryptor'];
    }

    public static function csrf(): CsrfProtection
    {
        return self::$services['csrf'];
    }

    public static function komponenFungsiAset(): KomponenFungsiAset
    {
        return self::$services['komponenFungsiAset'];
    }
    public static function Linker(): Linker
    {
        return self::$services['Linker'];
    }
    
    public static function D_AyaNe(): D_AyaNe
    {
        return self::$services['D_AyaNe'];
    }
    
    public static function D_SeEm(): D_SeEm
    {
        return self::$services['D_SeEm'];
    }
    
    public static function D_TeHa(): D_TeHa
    {
        return self::$services['D_TeHa'];
    }
}

interface DirectiveInterface
{
    public function process(string $content): string;
}

class ForDirective implements DirectiveInterface
{
    public function process(string $content): string
    {
        return preg_replace_callback('/@for\(([^)]+)\)/', function ($matches) {
            if (empty($matches[1])) {
                throw new \Exception("For directive requires a range.");
            }
            return "<?php for ({$matches[1]}) : ?>";
        }, $content);
    }
}

class CsrfDirective implements DirectiveInterface
{
    protected CsrfProtection $csrfProtection;

    public function __construct(CsrfProtection $csrfProtection)
    {
        $this->csrfProtection = $csrfProtection;
    }

    public function process(string $content): string
    {
        return preg_replace_callback('/@csrf\(([^)]+)\)/', function ($matches) {
            $formName = trim($matches[1], " '\"");
            return '<?php echo \Controllers\Controller::csrf()->generateTokenField(' . var_export($formName, true) . '); ?>';
        }, $content);
    }
}

class IncludeDirective implements DirectiveInterface
{
    protected array $services;
    protected string $viewsBaseDir;
    protected string $templateCacheDir;

    public function __construct(array $services, string $viewsBaseDir, string $templateCacheDir)
    {
        $this->services = $services;
        $this->viewsBaseDir = $viewsBaseDir;
        $this->templateCacheDir = $templateCacheDir;
    }

    public function process(string $content): string
    {
        return preg_replace_callback('/@include\(([^)]+)\)/', function ($matches) {
            if (empty($matches[1])) {
                throw new \Exception("Include directive requires a file path.");
            }

            $includePath = trim($matches[1], " '");
            $includeFile = $this->viewsBaseDir . '/' . $includePath . '.sora.php';

            if (!file_exists($includeFile)) {
                throw new \Exception("Include file {$includePath} not found.");
            }

            $cacheFile = $this->templateCacheDir . '/' . md5($includeFile) . '.php';

            if (!$this->isCacheValid($includeFile, $cacheFile)) {
                $compiledContent = self::compileTemplate(file_get_contents($includeFile), $includeFile);
                file_put_contents($cacheFile, $compiledContent);
            }

            return '<?php include "' . $cacheFile . '"; ?>';
        }, $content);
    }

    protected function isCacheValid(string $includeFile, string $cacheFile): bool
    {
        return file_exists($cacheFile) && filemtime($includeFile) < filemtime($cacheFile);
    }

    protected static function compileTemplate(string $templateContent, string $viewFile): string
    {
        return Controller::processDirectives($templateContent, $viewFile);
    }

    
}

class IncludeOnceDirective extends IncludeDirective
{
    public function process(string $content): string
    {
        return preg_replace_callback('/@include_once\(([^)]+)\)/', function ($matches) {
            if (empty($matches[1])) {
                throw new \Exception("Include_once directive requires a file path.");
            }

            $includePath = trim($matches[1], " '");
            $includeFile = $this->viewsBaseDir . '/' . $includePath . '.sora.php';

            if (!file_exists($includeFile)) {
                throw new \Exception("Include_once file {$includePath} not found.");
            }

            $cacheFile = $this->templateCacheDir . '/' . md5($includeFile) . '.php';

            if (!$this->isCacheValid($includeFile, $cacheFile)) {
                $compiledContent = $this->compileTemplate(file_get_contents($includeFile), $includeFile);
                file_put_contents($cacheFile, $compiledContent);
            }

            return '<?php include_once "' . $cacheFile . '"; ?>';
        }, $content);
    }
}

class RequireDirective extends IncludeDirective
{
    public function process(string $content): string
    {
        return preg_replace_callback('/@require\(([^)]+)\)/', function ($matches) {
            if (empty($matches[1])) {
                throw new \Exception("Require directive requires a file path.");
            }

            $includePath = trim($matches[1], " '");
            $includeFile = $this->viewsBaseDir . '/' . $includePath . '.sora.php';

            if (!file_exists($includeFile)) {
                throw new \Exception("Require file {$includePath} not found.");
            }

            $cacheFile = $this->templateCacheDir . '/' . md5($includeFile) . '.php';

            if (!$this->isCacheValid($includeFile, $cacheFile)) {
                $compiledContent = $this->compileTemplate(file_get_contents($includeFile), $includeFile);
                file_put_contents($cacheFile, $compiledContent);
            }

            return '<?php require "' . $cacheFile . '"; ?>';
        }, $content);
    }
}

class RequireOnceDirective extends IncludeDirective
{
    public function process(string $content): string
    {
        return preg_replace_callback('/@require_once\(([^)]+)\)/', function ($matches) {
            if (empty($matches[1])) {
                throw new \Exception("Require_once directive requires a file path.");
            }

            $includePath = trim($matches[1], " '");
            $includeFile = $this->viewsBaseDir . '/' . $includePath . '.sora.php';

            if (!file_exists($includeFile)) {
                throw new \Exception("Require_once file {$includePath} not found.");
            }

            $cacheFile = $this->templateCacheDir . '/' . md5($includeFile) . '.php';

            if (!$this->isCacheValid($includeFile, $cacheFile)) {
                $compiledContent = $this->compileTemplate(file_get_contents($includeFile), $includeFile);
                file_put_contents($cacheFile, $compiledContent);
            }

            return '<?php require_once "' . $cacheFile . '"; ?>';
        }, $content);
    }
}

class ForeachDirective implements DirectiveInterface
{
    public function process(string $content): string
    {
        return preg_replace_callback('/@foreach\(([^)]+)\)/', function ($matches) {
            if (empty($matches[1])) {
                throw new \Exception("Foreach directive requires an iterable expression.");
            }
            return '<?php foreach(' . $matches[1] . ') : ?>';
        }, $content);
    }
}

class EndForeachDirective implements DirectiveInterface
{
    public function process(string $content): string
    {
        return preg_replace('/@endforeach/', '<?php endforeach; ?>', $content);
    }
}

class IfDirective implements DirectiveInterface
{
    public function process(string $content): string
    {
        return preg_replace_callback('/@if\s*\((.*)\)/', function ($matches) {
            if (empty($matches[1])) {
                throw new \Exception("If directive requires a condition.");
            }
            return "<?php if ({$matches[1]}) : ?>";
        }, $content);
    }
}

class ElseIfDirective implements DirectiveInterface
{
    public function process(string $content): string
    {
        return preg_replace_callback('/@elseif\s*\((.*)\)/', function ($matches) {
            if (empty($matches[1])) {
                throw new \Exception("Elseif directive requires a condition.");
            }
            return "<?php elseif ({$matches[1]}) : ?>";
        }, $content);
    }
}

class ElseDirective implements DirectiveInterface
{
    public function process(string $content): string
    {
        return preg_replace('/@else/', '<?php else: ?>', $content);
    }
}

class EndIfDirective implements DirectiveInterface
{
    public function process(string $content): string
    {
        return preg_replace('/@endif/', '<?php endif; ?>', $content);
    }
}

class EndforDirective implements DirectiveInterface
{
    public function process(string $content): string
    {
        return preg_replace('/@endfor/', '<?php endfor; ?>', $content);
    }
}

class EchoDirective implements DirectiveInterface
{
    public function process(string $content): string
    {
        return preg_replace_callback('/{{([^}]+)}}/', function ($matches) {
            if (empty($matches[1])) {
                throw new \Exception("Echo directive requires a variable.");
            }
            return '<?php echo ' . $matches[1] . '; ?>';
        }, $content);
    }
}

class PhpDirective implements DirectiveInterface
{
    public function process(string $content): string
    {
        return preg_replace_callback('/@php(.*?)@endphp/s', function ($matches) {
            return '<?php ' . trim($matches[1]) . ' ?>';
        }, $content);
    }
}

class FlashDirective implements DirectiveInterface
{
    public function process(string $content): string
    {
        return preg_replace_callback('/@flash\(([^)]+)\)/', function ($matches) {
            if (empty($matches[1])) {
                throw new \Exception("Flash directive requires a key.");
            }

            $key = trim($matches[1], " '");

            return "<?php if (\Contingent\D_SeEm::has('{$key}')) : ?>
                        <?php
                            \$flashData = \Contingent\D_SeEm::get('{$key}');
                            \$message = is_array(\$flashData) ? \$flashData['message'] : '';
                            \$type = is_array(\$flashData) && isset(\$flashData['type']) ? \$flashData['type'] : 'info';
                        ?>
                        <div class=\"alert alert-<?= htmlspecialchars(\$type, ENT_QUOTES, 'UTF-8') ?>\" role=\"alert\">
                            <?= htmlspecialchars(\$message, ENT_QUOTES, 'UTF-8') ?>
                        </div>
                    <?php endif; ?>";
        }, $content);
    }
}


class CaptchaDirective implements DirectiveInterface
{
    protected Captha $captchaProtection;

    public function __construct(Captha $captchaProtection)
    {
        $this->captchaProtection = $captchaProtection;
    }

    public function process(string $content): string
    {
        $protection = $this->captchaProtection;
        return preg_replace_callback('/@captcha/', function () use ($protection) {
            return '<?php echo ' . get_class($protection) . '::displayCaptchaForm(); ?>';
        }, $content);
    }
}
