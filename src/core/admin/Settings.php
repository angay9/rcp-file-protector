<?php

namespace RcpFileProtector\Core\Admin;

use RcpFileProtector\Core\View;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Settings 
{
    protected $view;

    public function __construct(View $view)
    {
        $this->view = $view;
    }

    public function init()
    {
        add_action('admin_menu', [$this, 'addMenuPage']);
    }

    public function addMenuPage()
    {
        $page = add_menu_page(
            'Restrict Content Pro File Protector', 
            'RCP File Protector', 
            'manage_options', 
            'rcp-file-protector', 
            [$this, 'renderOptionsPage'],
            'dashicons-hidden'
        );

        add_action('admin_print_scripts-' . $page, [$this, 'addAdminAssets']);
    }

    public function addAdminAssets()
    {

        wp_register_script(
            'rcp-file-protector', 
            RCP_FILE_PROTECTOR_ROOT_URL . '/assets/dist/js/app.js', 
            ['jquery'], 
            '', 
            true
        );

        wp_enqueue_script('rcp-file-protector');
        wp_enqueue_style(
            'rcp-file-protector', 
            RCP_FILE_PROTECTOR_ROOT_URL . '/assets/dist/css/rcp-file-protector.css' 
        );
    }

    public function renderOptionsPage()
    {
        $errors = [];
        $successMessage = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $this->getPostData();

            $errors = $this->validate($data);

            if (count($errors) == 0) {
                $this->saveOptions($data);
                $successMessage = 'Options have been saved.';
            }
        }

        $memberships = array_map(function($level) {
            return [
                'id' => $level->id,
                'name' => $level->name
            ];
        }, rcp_get_subscription_levels());

        $view = $this->view->render(
            'admin/index.php',
            [
                'successMessage' => $successMessage,
                'errors' => $errors,
                'memberships' => $memberships,
                'protectionLevels' => stripslashes_deep(get_option('rcp_file_protector_protection_levels', []))
            ]
        );

        echo $view;
    }

    protected function saveOptions(array $data) 
    {
        update_option('rcp_file_protector_protection_levels', $data['levels'], true);
    }

    protected function validate(array $data) 
    {
        $errors = [];

        // Validate nonce
        if (! wp_verify_nonce( $data['nonce'], 'rcp-file-protector_save-options' )) {
            wp_die('invalid nonce');
            $errors['nonce'] = ['Invalid nonce. Please refresh the page and try again'];
        }

        // Validate required memberships field and url

        $levelErrors = [];
        foreach ($data['levels'] as $index => $level) {
            $humanIndex = $index + 1;

            if (! isset($level['memberships']) || count($level['memberships']) == 0) {
                $levelErrors[] = "Protection level #{$humanIndex}: memberships can't be empty";
            }

            if (! $level['url']) {
                $levelErrors[] = "Protection level #{$humanIndex}: URL can't be empty.";
            }
        }

        if (count($levelErrors) > 0) {
            $errors['levels'] = $levelErrors;
        }

        return $errors;
    }

    protected function getPostData()
    {
        $levels = isset($_POST['levels']) ? $_POST['levels'] : [];

        $levels = array_map(function ($level) {

            $level = array_merge($level, [
                'isRegex' => isset($level['isRegex']) ? true : false
            ]);

            return $level;
        }, $levels);

        $data = [
            'nonce' => isset($_POST['rcp-file-protector_nonce']) ? $_POST['rcp-file-protector_nonce'] : null,
            'levels' => $levels
        ];

        return $data;
    }
}