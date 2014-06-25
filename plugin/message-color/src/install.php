<?php
/**
 * Install plugin hooks
 */
use ElfChat\Plugin\Hook;

Hook::view('chat/chat.twig')->block('buttons')->append(__DIR__ . '/../hooks/color_button.twig');
Hook::view('chat/script.twig')->block('popovers')->append(__DIR__ . '/../hooks/color_popover.twig');
