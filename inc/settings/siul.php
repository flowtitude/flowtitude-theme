<?php
if (class_exists('SIUL')):
    function scanner_flowtitude_provider(): array {
        $file_extensions = [
            'php',
            'js',
            'twig',
            'html',
        ];

        $contents = [];
        $finder = new \_YabeSiul\Symfony\Component\Finder\Finder();
        $wpTheme = wp_get_theme();
        $themeDir = $wpTheme->get_stylesheet_directory();
        
        $has_parent = $wpTheme->parent() ? true : false;
        $parentThemeDir = $wpTheme->parent()->get_stylesheet_directory() ?? null;
        foreach ($file_extensions as $extension) {
            $finder->files()->in($themeDir)->name('*.' . $extension);
            if ($has_parent) {
                $finder->files()->in($parentThemeDir)->name('*.' . $extension);
            }
        }
        foreach ($finder as $file) {
            $contents[] = [
                'name' => $file->getRelativePathname(),
                'content' => $file->getContents(),
            ];
        }

        return $contents;
    }
    function register_flowtitude_provider(array $providers): array {
        $providers[] = [
            'id' => 'flowtitude-child-theme',
            'name' => 'Flowtitude Scanner',
            'description' => 'Scans the current active theme',
            'callback' => 'scanner_flowtitude_provider', // The function that will be called to get the data
            'enabled' => \Yabe\Siul\Utils\Config::get(sprintf(
                'integration.%s.enabled',
                'flowtitude-child-theme' // The id of this custom provider
            ), true),
        ];

        return $providers;
    }

    add_filter('f!yabe/siul/core/cache:compile.providers', 'register_flowtitude_provider');
endif;