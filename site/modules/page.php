<?php
class Page {
    private $template;

    public function __construct($template) {
        $this->template = $template;
    }

    public function Render($data) {
        if (!file_exists($this->template)) {
            return "Template not found";
        }
        $content = file_get_contents($this->template);
        foreach ($data as $key => $value) {
            $content = str_replace("{{{$key}}}", $value, $content);
        }
        return $content;
    }
}