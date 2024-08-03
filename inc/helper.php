<?php 

function ft_load_resources($dir) {
    $resources = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
  
    foreach ($resources as $resource) {
        if ($resource->isDir()) {
            continue;
        }
        if (pathinfo($resource->getFilename(), PATHINFO_EXTENSION) !== 'php') {
          continue;
        }
        if (strpos($resource->getFilename(), '_') === 0) {
            continue;
        }
        require_once $resource->getPathname();
    }
  }

function ft_enqueue_recursive_assets($directory) {
    $dir = get_stylesheet_directory() . '/' . $directory; // Directorio del tema hijo
    $url = get_stylesheet_directory_uri() . '/' . $directory;

    if (is_dir($dir)) {
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
        foreach ($files as $file) {
            if ($file->isFile()) {
                $file_name = $file->getFilename();
                $file_path = str_replace($dir, '', $file->getPathname());
                $file_url = $url . $file_path;
                if ($file_name[0] !== '_' && preg_match('/\.(css|js)$/i', $file_name)) {
                    if (preg_match('/\.css$/i', $file_name)) {
                        wp_enqueue_style('ft-' . md5($file_url), $file_url);
                    }
                    if (preg_match('/\.js$/i', $file_name)) {
                        wp_enqueue_script('ft-' . md5($file_url), $file_url, array(), null, true);
                    }
                }
            }
        }
    }
}

function get_php_files($dir) {
    
    $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    $files = [];
    foreach ($rii as $file) {
        if (!$file->isDir() && $file->getExtension() === 'php' && strpos($file->getFilename(), '_') !== 0) {
            $files[] = $file->getPathname();
        }
    }
    return $files;
}