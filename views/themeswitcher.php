<?php defined('APPLICATION') or die; ?>
<style>
.ThemeSwitcher {
  position: absolut;
  top:10px;
  right:-50px;
  width:10em;
}
</style>
<aside class="ThemeSwitcher">
  <h2><?= t('Choose a theme') ?></h2>
  <div class="FormWrapper">
  <?php
    echo $sender->Form->open(
        [
            'action' => url('plugin/themeswitcher'),
            'method' => 'GET'
        ]
    ),
      $sender->Form->errors(),
      $sender->Form->dropDown(
          'UserTheme',
          $sender->data('ThemeSwitcherStyles'),
          [
            'IncludeNull' => true,
            'ValueField' => 'Name',
            'TextField' => 'Name'

          ]
      ),
      $sender->Form->button('Go'),
      $sender->Form->close();
  ?>
  </div>
</aside>