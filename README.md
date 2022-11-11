# dalle

ExpressionEngine add-on integration with Dall-E 2

Type in a phrase to have the Dall-E image AI generate an image, unique each time. Created images are automaticaly added to the EE file manager and phrases are cached.

## Tag

Template tags are listed below.

```php
{exp:dalle:image
  phrase="software developer throwing a computer into a dumpster on fire like the movie office space"
}
  <img src="{url}" />
{/exp:dalle:image}
```

Use with a custom text field

```php
{exp:dalle:image
  phrase="{image_phrase}"
}
  <img src="{url}" />
{/exp:

## Parameters

| Name | Description | Default |
| -----|-------------|---------|
| phrase | (Required) Text to describe the image to create.  Max 1,000 characters | empty |
| cache | When set to `true` will search the log for the most recent image generated from the exact phase. `false` will always generate a new image. | `true` |
| size | Size of the image to create.  Currently there are only 3 sizes.  Costs increase for larger images. | 256x256 |

## Tags
