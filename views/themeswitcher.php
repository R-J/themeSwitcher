<?php defined('APPLICATION') or die; ?>
<style>
.ThemeSwitcher {
  color:#eee;
  position: fixed;
  bottom:0;
  right:0;
  background:rgba(222,222,222,0.8);
  padding:5px
}
.ThemeSwitcher input {
  width:100%;
}
</style>
<aside class="ThemeSwitcher">
  <div class="FormWrapper">
  <?php
    echo $sender->Form->open(
        [
            'action' => url('plugin/themeswitcher'),
            'method' => 'GET'
        ]
    ),
      $sender->Form->errors(),
      $sender->Form->label('Choose a theme', 'UserTheme'),
      $sender->Form->dropDown(
          'UserTheme',
          $sender->data('ThemeSwitcherStyles'),
          [
            'IncludeNull' => true,
            'ValueField' => 'basename',
            'TextField' => 'basename'
          ]
      ),
      $sender->Form->button('Go'),
      $sender->Form->close();
  ?>
  </div>
</aside>