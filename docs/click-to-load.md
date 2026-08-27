# Click-to-load embeds

By default, a Slots Launch embed loads the game iframe as soon as the page is ready. For better performance — or when you want a custom thumbnail and play button — use **click-to-load** mode.

Set `autoload="false"` on the shortcode. The plugin outputs the embed container in HTML but does **not** fetch or mount the iframe until the visitor clicks play.

## Basic usage

Empty container — wire up your own UI in the theme or with JavaScript:

```
[slotslaunch_game id="45958" autoload="false"]
```

## Placeholder with play button

Put your placeholder HTML inside the shortcode. Add `data-sl-play` to the element that should start the game (usually a button or link):

```
[slotslaunch_game id="45958" autoload="false" height="600" width="100%"]
<div class="game-placeholder">
  <img src="/wp-content/uploads/game-thumb.jpg" alt="Play demo" width="800" height="450" />
  <button type="button" data-sl-play>Play demo</button>
</div>
[/slotslaunch_game]
```

When the visitor clicks the button, the placeholder is replaced by the game iframe.

## JavaScript API

For full control, call `SlotsLaunchEmbeds.load()` from your own script:

```javascript
// Pass the embed container
SlotsLaunchEmbeds.load(document.querySelector('.slotslaunch-embed'));

// Or pass a child element (button, wrapper, etc.)
document.getElementById('my-play-btn').addEventListener('click', function () {
  SlotsLaunchEmbeds.load(this);
});
```

The global `SlotsLaunchEmbeds.load()` accepts:

- A DOM element (the `.slotslaunch-embed` container or any element inside it)
- A CSS selector string (e.g. `'#game-45958'`)

## Shortcode attributes

| Attribute   | Default | Description |
|------------|---------|-------------|
| `id`       | —       | Game ID (required). Alias: `game`. |
| `height`   | `600`   | Embed height (`600`, `600px`, `80vh`, etc.). |
| `width`    | `100%`  | Container width. |
| `autoload` | `true`  | Set to `false` to defer loading until click. |

Accepted values for `autoload`: `true` / `false`, `1` / `0`, `yes` / `no`, `on` / `off`.

## HTML output (manual mode)

When `autoload="false"`, the shortcode renders something like:

```html
<div
  class="slotslaunch-embed slotslaunch-embed--manual"
  data-sl-game="45958"
  data-sl-height="600px"
  data-sl-title="Slots Launch game 45958"
  data-sl-autoload="0"
  style="width:100%;max-width:100%;min-height:600px;"
>
  <!-- your placeholder HTML -->
</div>
```

Embeds with `data-sl-autoload="0"` are skipped on page load. The iframe URL is still signed server-side via AJAX when load is triggered, so full-page caching remains safe.

## Tips

- Style `.slotslaunch-embed--manual` and your placeholder however you like; the iframe replaces the inner HTML when the game starts.
- Use `type="button"` on play buttons inside forms so they do not submit the form.
- `data-sl-play` works on any clickable element inside the embed (`<button>`, `<a>`, `<div>`, etc.).
