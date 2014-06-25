<?php
/**
 * Install plugin hooks
 */
use ElfChat\Plugin\Hook;

Hook::view('chat/chat.twig')->block('buttons')->append('@message-color/button.twig');
Hook::view('chat/chat.twig')->block('head')->append('@message-color/head.twig');
Hook::view('chat/script.twig')->block('popovers')->append('@message-color/popover.twig');
Hook::view('chat/script.twig')->block('script')->append('@message-color/script.twig');