<?php

use Shaarli\Plugin\PluginManager;

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
			$text = trim($_POST['lf_buffer_text']);
		}

		$data['buffer_text'] = $text;

		if (empty($text))
		{
			$buffer_text = $data['title'] . ' ' . $data['url'];
		}
		else
		{
			if (!preg_match('/\[url\]/i', $text))
			{
				$buffer_text += " {$data['url']}";
			}
			$buffer_text = str_replace(['[url]', '[orig]'], [$data['url'], $data['original_url']], $text);
		}

		$now = false;
		$top = false;
		$lf_buffer_strategy = escape($_POST['lf_buffer_strategy']);

		switch ($lf_buffer_strategy)
		{
			case 'now':
				$now = true;
				break;
			case 'rand10m':
				$date = (new DateTime())->add(new DateInterval(sprintf("PT%dM", mt_rand(5,15))));
				$lf_buffer_strategy = 'scheduled_at';
				break;
			case 'rand1h':
				$date = (new DateTime())->add(new DateInterval(sprintf("PT%dM", mt_rand(40,90))));
				$lf_buffer_strategy = 'scheduled_at';
				break;
			case 'today':
				$date = (new DateTime())->add(new DateInterval(sprintf("PT%dM", mt_rand(40,90))));
				$lf_buffer_strategy = 'scheduled_at';
				break;
			case 'tomorrow':
				$date = (new DateTime())->add(new DateInterval(sprintf("PT%dM", mt_rand(40,90))));
				$lf_buffer_strategy = 'scheduled_at';
				break;
			case 'rand4h':
				$date = (new DateTime())->add(new DateInterval(sprintf("PT%dM", mt_rand(90,300))));
				$lf_buffer_strategy = 'scheduled_at';
				break;
			case 'schedule':
				// noop
				break;
			case 'schedule_top':
				$top = true;
				break;
			default:
				$lf_buffer_strategy = 'schedule';
				break;
		}

		$retweet = [];

		// Optional part: handle retweets, depends on via plugin
		if (!empty($_POST['lf_original_url']))
		{
			$parts = parse_url($_POST['lf_original_url']);

			if (in_array($parts['host'], ['twitter.com', 'mobile.twitter.com']) &&
				preg_match('@^/.+/status(?:es)?/([0-9]+)$@', escape($parts['path']), $statuses))
			{
				$retweet['retweet[tweet_id]'] = $statuses[1];

				if (!empty($text))
				{
					$retweet['retweet[comment]'] = str_replace([' [url]'], [''], trim($text));
				}
			}
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

			$update = [
				'shorten' => false,
				'now' => $now,
				'top' => $top,
			];

			if ($lf_buffer_strategy === 'scheduled_at')
			{
				$update['scheduled_at'] = $date->format('c');
			}

			foreach($profiles_id as $profile)
			{
				$update['profile_ids[]'] = $profile;

				if (!empty($retweet))
				{
					$update = array_merge($update, $retweet);
				}
				else
				{
					$update['text'] = $buffer_text;
				}

				try {
					$resp = $buffer_app->go('/updates/create', $update);
				} catch (\Exception $e) {
					echo "Buffer returned an error: {$e->getMessage()}";
					trigger_error($e, E_USER_ERROR);
				}

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
