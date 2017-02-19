# Shaarli Buffer

Shaarli Buffer is a small plugin which enables you to post links to Buffer with custom text.

It adds 2 fields in the link edition page for the text and the sharing strategy.

Current strategies are:
* `schedule` which appends the update to the normal schedule of the profile
* `now` which instructs Buffer to share the update immediately
* `ignore` which ignores the share, default

When a link has been shared, update ids are saved and the form is replaced with a single line.

Interaction with the Buffer API is done using an embedded copy of [bufferapp-php](https://github.com/thewebguy/bufferapp-php).

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

**Note:** this plugin is really simple, does not handle exceptions and could be improved.
