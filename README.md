# Shaarli Buffer

Shaarli Buffer is a small plugin which enables you to post links to Buffer with custom text.

It adds 2 fields in the link edition page for the text and the sharing strategy.

Current strategies are:
* `schedule` which appends the update to the normal schedule of the profile
* `schedule on top` which adds the update on the top of the normal schedule
* `now` which instructs Buffer to share the update immediately
* `± 10 minutes` which instructs Buffer to share the update in 5 to 15 minutes
* `± 10 hour` which instructs Buffer to share the update in 45 to 75 minutes
* `ignore` which ignores the share, default

When a link has been shared, update ids are saved and the form is replaced with a warning.

The link is automatically appended with a white space to the value of text field. Also if the text field is empty, the plugin will use the link title instead.

Interaction with the Buffer API is done using an embedded copy of [bufferapp-php](https://github.com/thewebguy/bufferapp-php).

### Retweets

This plugin is able to schedule retweets instead of normal statuses if via plugin is enabled and it finds a twitter status link as the original source of a link.

### Limitations

This plugin does not handle exceptions and silently fails. For example the update post will silently fail if the text is larger than the tweet limit.

Also the retweet feature considers that only twitter profiles are configured for Buffer sharing and will produce empty updates for the other social networks.

## Installation
### Via Git

If you use git you can run the following command from within the `plugins` folder of your Shaarli installation:

```shell
git clone https://github.com/Kdecherf/shaarli-plugin-buffer buffer
```

### Manually
Create the folder `plugins/buffer` in your Shaarli installation and copy all the files in it.

## Activation
Then, activate the plugin through the plugin administration panel.

## Buffer App

You must create a Buffer App in order to be able to post updates on your Buffer account.

Go to [Buffer Developers](https://buffer.com/developers/apps) and create a new app.

After creating your application, you will receive an email with the client secret and a unique access token will be available on the app page.

## Configuration

After creating a Buffer App you must add the following lines to your `data/config.json.php` file:

```json
   "config": {
      "BUFFER_CLIENT_ID": "yourclientid",
      "BUFFER_CLIENT_SECRET": "yourclientsecret",
      "BUFFER_ACCESS_TOKEN": "youraccesstoken",
      "BUFFER_PROFILES_ID": ["yourprofileid"]
   }
```

The profile id can be retrieved from the attribute `data-id` in the account switcher of Buffer.

**Note:** this plugin is quite simple and stupid, it throws dirty exceptions and could be improved.
