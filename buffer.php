<?php

include_once PluginManager::$PLUGINS_PATH . '/buffer/bufferapp.php';

/**
 * Hook render_editlink
 * Template placeholders:
 *   - field_plugins: add buffer fields after tags
 *
 * @param array $data data passed to plugin
 *
 * @return array altered $data
 */
function hook_buffer_render_editlink($data)
{
   $form = file_get_contents(PluginManager::$PLUGINS_PATH . '/buffer/fields.html');
   $posted = file_get_contents(PluginManager::$PLUGINS_PATH . '/buffer/posted.html');
   $html = isset($data['link']['buffer_update_ids']) ? $posted : $form;

   $data['edit_link_plugin'][] = $html;

   return $data;
}

/**
 * Hook savelink
 *
 * Triggered when a link is saved (new or edit).
 * Will create an update on buffer.com is requested
 * and no previous update was created for this link
 *
 * @param array $data contains the new link data
 *
 * @return array altered $data
 */
function hook_buffer_save_link($data)
{
   global $conf;

   if (!isset($data['buffer_update_ids']) &&
         !empty($_POST['lf_buffer_strategy']) &&
         $_POST['lf_buffer_strategy'] !== 'ignore')
   {
      if (!empty($_POST['lf_buffer_text']))
      {
         $text = escape($_POST['lf_buffer_text']);
      }
      else
      {
         $text = $data['title'];
      }

      $data['buffer_text'] = $text;

      $buffer_text = $text . ' ' . $data['url'];

      $now = false;
      $lf_buffer_strategy = escape($_POST['lf_buffer_strategy']);

      switch ($lf_buffer_strategy)
      {
         case 'now':
            $now = true;
            break;
         case 'schedule':
            // noop
            break;
         default:
            $lf_buffer_strategy = 'schedule';
            break;
      }

      $client_id = $conf->get('config.BUFFER_CLIENT_ID');
      $client_secret = $conf->get('config.BUFFER_CLIENT_SECRET');
      $access_token = $conf->get('config.BUFFER_ACCESS_TOKEN');
      $profiles_id = $conf->get('config.BUFFER_PROFILES_ID');

      if (!empty($client_id) && !empty($client_secret) &&
            !empty($access_token) && !empty($profiles_id))
      {
         $buffer_app = new BufferApp($client_id, $client_secret, null, $access_token);
         $update_ids = [];

         foreach($profiles_id as $profile)
         {
            $resp = $buffer_app->go('/updates/create', [
                  'text' => $buffer_text,
                  'shorten' => false,
                  'now' => $now,
                  'profile_ids[]' => $profile
            ]);

            if (isset($resp->updates[0]))
            {
               $update_ids[] = $resp->updates[0]->id;
            }
         }

         if (sizeof($update_ids) > 0)
         {
            $data['buffer_update_ids'] = $update_ids;
         }
      }
   }

   return $data;
}
