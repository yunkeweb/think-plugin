<?php
declare (strict_types=1);

namespace yunkeweb\plugin\command;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;

class Build extends Command
{
    /**
     * 插件基础目录
     * @var string
     */
   protected $basePath;

    /**
     * 插件中文名称
     * @var string
     */
   protected $name;

    /**
     * 插件描述
     * @var string
     */
   protected $description;

    /**
     * 插件作者
     * @var string
     */
   protected $author;

    /**
     * 插件作者邮箱
     * @var string
     */
   protected $email;

   protected function configure()
   {
       $this->setName('plugin:build')
           ->addArgument('plugin', Argument::OPTIONAL, 'plugin name .')
           ->addOption('name',null,Option::VALUE_OPTIONAL,'插件中文名称')
           ->addOption('desc',null,Option::VALUE_OPTIONAL,'插件描述')
           ->addOption('author',null,Option::VALUE_OPTIONAL,'插件作者')
           ->addOption('email',null,Option::VALUE_OPTIONAL,'插件作者邮箱')
           ->setDescription('Build Plugin Dirs');
   }

   protected function execute(Input $input, Output $output)
   {
       $this->basePath = $this->app->getRootPath() . 'plugin' . DIRECTORY_SEPARATOR;
       $plugin         = $input->getArgument('plugin') ?: '';
       if ($plugin == ''){
           $output->error("请传入插件名称");
           return;
       }
       if ($input->getOption('name')){
           $this->name = $input->getOption('name');
       }
       if ($input->getOption('desc')){
           $this->description = $input->getOption('desc');
       }
       if ($input->getOption('author')){
           $this->author = $input->getOption('author');
       }
       if ($input->getOption('email')){
           $this->email = $input->getOption('email');
       }

       if (is_file($this->basePath . 'build.php')) {
           $list = include $this->basePath . 'build.php';
       } else {
           $list = [
               '__dir__' => ['controller', 'model', 'view'],
           ];
       }
       $this->buildPlugin($plugin, $list);
       $output->writeln("<info>Successed</info>");
   }

   protected function buildPlugin(string $plugin,array $list = [])
   {
       if (!is_dir($this->basePath . $plugin)){
           // 创建插件目录
           mkdir($this->basePath . $plugin);
       }

       $pluginPath = $this->basePath . $plugin . DIRECTORY_SEPARATOR;
       $namespace  = 'plugin' . '\\' . $plugin;

       // 创建配置文件和公共文件
       $this->buildCommon($plugin);
       // 创建插件的默认页面
       $this->buildHello($plugin, $namespace);
       // 创建config.json文件
       $this->buildConfigJson($plugin);
       foreach ($list as $path => $file) {
           if ('__dir__' == $path) {
               // 生成子目录
               foreach ($file as $dir) {
                   $this->checkDirBuild($pluginPath . $dir);
               }
           } elseif ('__file__' == $path) {
               // 生成（空白）文件
               foreach ($file as $name) {
                   if (!is_file($pluginPath . $name)) {
                       file_put_contents($pluginPath . $name, 'php' == pathinfo($name, PATHINFO_EXTENSION) ? '<?php' . PHP_EOL : '');
                   }
               }
           } else {
               // 生成相关MVC文件
               foreach ($file as $val) {
                   $val      = trim($val);
                   $filename = $pluginPath . $path . DIRECTORY_SEPARATOR . $val . '.php';
                   $space    = $namespace . '\\' . $path;
                   $class    = $val;
                   switch ($path) {
                       case 'controller': // 控制器
                           if ($this->app->config->get('route.controller_suffix')) {
                               $filename = $pluginPath . $path . DIRECTORY_SEPARATOR . $val . 'Controller.php';
                               $class    = $val . 'Controller';
                           }
                           $content = "<?php" . PHP_EOL . "namespace {$space};" . PHP_EOL . PHP_EOL . "class {$class}" . PHP_EOL . "{" . PHP_EOL . PHP_EOL . "}";
                           break;
                       case 'model': // 模型
                           $content = "<?php" . PHP_EOL . "namespace {$space};" . PHP_EOL . PHP_EOL . "use think\Model;" . PHP_EOL . PHP_EOL . "class {$class} extends Model" . PHP_EOL . "{" . PHP_EOL . PHP_EOL . "}";
                           break;
                       case 'view': // 视图
                           $filename = $pluginPath . $path . DIRECTORY_SEPARATOR . $val . '.html';
                           $this->checkDirBuild(dirname($filename));
                           $content = '';
                           break;
                       default:
                           // 其他文件
                           $content = "<?php" . PHP_EOL . "namespace {$space};" . PHP_EOL . PHP_EOL . "class {$class}" . PHP_EOL . "{" . PHP_EOL . PHP_EOL . "}";
                   }

                   if (!is_file($filename)) {
                       file_put_contents($filename, $content);
                   }
               }
           }
       }
   }

   protected function buildConfigJson(string $plugin)
   {
       $filename = $this->basePath . $plugin . DIRECTORY_SEPARATOR . 'config.json';

       if (!is_file($filename)) {
           $content = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . 'build' . DIRECTORY_SEPARATOR . 'config.stub');
           $content = str_replace(['{%nameCn%}', '{%nameEn%}', '{%description%}', '{%name%}','{%email%}'], [$this->name, $plugin, $this->description, $this->author,$this->email], $content);
           $this->checkDirBuild(dirname($filename));

           file_put_contents($filename, $content);
       }
   }

    protected function buildHello(string $plugin, string $namespace): void
    {
        $suffix   = $this->app->config->get('route.controller_suffix') ? 'Controller' : '';
        $filename = $this->basePath . $plugin . DIRECTORY_SEPARATOR . 'controller' . DIRECTORY_SEPARATOR . 'Index' . $suffix . '.php';

        if (!is_file($filename)) {
            $content = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . 'build' . DIRECTORY_SEPARATOR . 'controller.stub');
            $content = str_replace(['{%name%}', '{%app%}', '{%layer%}', '{%suffix%}'], [$plugin, $namespace, 'controller', $suffix], $content);
            $this->checkDirBuild(dirname($filename));

            file_put_contents($filename, $content);
        }
    }

    protected function buildCommon(string $plugin): void
    {
        $pluginPath = $this->basePath . $plugin . DIRECTORY_SEPARATOR;

        if (!is_file($pluginPath . 'common.php')) {
            file_put_contents($pluginPath . 'common.php', "<?php" . PHP_EOL . "// 这是系统自动生成的公共文件" . PHP_EOL);
        }

        foreach (['event', 'middleware', 'common'] as $name) {
            if (!is_file($pluginPath . $name . '.php')) {
                file_put_contents($pluginPath . $name . '.php', "<?php" . PHP_EOL . "// 这是系统自动生成的{$name}定义文件" . PHP_EOL . "return [" . PHP_EOL . PHP_EOL . "];" . PHP_EOL);
            }
        }
    }

    /**
     * 创建目录
     * @access protected
     * @param  string $dirname 目录名称
     * @return void
     */
    protected function checkDirBuild(string $dirname): void
    {
        if (!is_dir($dirname)) {
            mkdir($dirname, 0755, true);
        }
    }
}