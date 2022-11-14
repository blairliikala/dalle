<style>
  .hidden {
    display: none
  }
  .image_grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(500px, 1fr));
    grid-column-gap: 15px;
    grid-row-gap: 15px;
  }
</style>

<div class="box panel">
  <div class="panel-heading">
      <div class="title-bar">
          <h3 class="title-bar__title title-bar--large"><?=$heading?></h3>
          <div class="filters-toolbar title-bar__extra-tools"></div>
      </div>
  </div>

  <div class="panel-body">
    <form id="generate_form">
      <fieldset>
        <div class="field-instruct">
          <label for="phrase">Phrase</label>
          <em>Type a descriptive phrase for the AI to generate an image. <span id="wordcount"><span id="ch_left">1000</span>/1000</span></em>
        </div>
        <div class="field-control">
          <input type="text" name="phrase" id="phrase" minlengh="5" maxlength="1000" required>
        </div>
      </fieldset>

      <fieldset>
        <div class="field-instruct">
          <label for="smth">Image Size</label>
          <em>The size of the image to be generated. Larger images take a few more moments, and have a slightly higher cost.</em>
        </div>
        <div class="field-control">
          <div class="fields-select">
            <div class="field-inputs">
              <label class="checkbox-label">
                <input type="radio" name="size" value="256x256" <?php if($size === '256x256'): ?>checked<?php endif; ?>>
                <div class="checkbox-label__text">256x256</div>
              </label>
              <label class="checkbox-label">
                <input type="radio" name="size" value="512x512" <?php if($size === '512x512'): ?>checked<?php endif; ?>>
                <div class="checkbox-label__text">512x512</div>
              </label>
              <label class="checkbox-label">
                <input type="radio" name="size" value="1024x1024" <?php if($size === '1024x1024'): ?>checked<?php endif; ?>>
                <div class="checkbox-label__text">1024x1024</div>
              </label>
            </div>
          </div>
        </div>
      </fieldset>

      <div class="panel-footer">
        <div class="form-btns">
            <button class="button button--primary" id="generate" type="submit" value="Generate">Generate</button>
        </div>
      </div>

    </form>
  </div>

  <aside id="loader" class="hidden panel-body">
    <?php echo ee('CP/Alert')->makeInline('loading')
        ->asLoading()
        ->withTitle('Generating...Leave this window open while running.')
        ->render();
    ?>
  </aside>
  <aside id="errors" class="hidden panel-body">
    <?php echo ee('CP/Alert')->makeInline('error_form')
        ->asIssue()
        ->withTitle('Error')
        ->addToBody('Something went wrong. Try again.')
        ->render();
    ?>
  </aside>

  <section id="results" class="image_grid panel-body"></section>

</div>

<script type="module">
  const input = document.querySelector('#phrase');
  const submit = document.querySelector('#generate');
  const loader = document.querySelector('#loader');
  const base_addon = "<?php echo $base_path ?>";
  const base_file = "<?php echo $base_file ?>";
  const results = document.querySelector('#results');
  const wordcount = document.querySelector('#wordcount');
  const countRemaining = wordcount.querySelector('#ch_left');

  submit.addEventListener('click', (e) => {
    e.preventDefault();
    const value = input.value;
    if (!value) return;
    generateImage(value);
  });

  input.addEventListener('keyup', () => {
    const total = 1000;
    countRemaining.innerText = total - input.value.length;
  });

  async function generateImage(phrase) {
    results.innerHTML = '';
    hideError();
    showLoading();
    const params = {
      method: 'get',
      phrase: encodeURIComponent(phrase),
      size: getSizeValue(),
      cache: false
    };
    const url = buildUrl(base_addon, params);
    const images = await http(url);
    hideLoading();

    if (!images) {
      showError('Error','There was a problem making the request.  Check the console for more details.');
      return;
    }

    if ('error' in images[0]) {
      showError();
      console.debug("Images", images);
      setError('Error', images[0].error.message);
      return;
    }

    results.innerHTML = buildResultsHTML(images);
  }


  async function http(url) {
      if (!url) return false;

      let headers = new Headers();
      headers.append('pragma', 'no-cache');
      headers.append('cache-control', 'no-cache');

      const response = await fetch(url, {headers: headers})
          .catch(error => {
              console.debug('Error Fetching URL.', {url}, {error});
              return false;
          });

      if (!response) return false;

      if (!response.ok) {
          console.error("Fetch Error", {response}, {url});
      };

      try {
          return await response.clone().json();
      } catch(error) {
          let text = await response.clone().text();
          switch(true) {
              case text.includes('Log In | ExpressionEngine') :
                  console.debug({error}, {text});
                  break;

              case text.includes('ParseError Caught') :
                  console.debug("Possible PHP error.", {error}, {text});
                  break;

              case text.includes('<!doctype html>') :
                  console.debug("Returned HTML.", {error}, {text});
                  break;

              default:
                  console.error({error}, {text});
          }
          return false;
      }
  }

  function buildUrl(base, params) {
    const newParams = new URLSearchParams(params);
    const url = `${base}&${newParams.toString()}`;
    return url;
  }

  function showLoading() {
    loader.classList.remove('hidden');
  }

  function hideLoading() {
    loader.classList.add('hidden');
  }

  function showError() {
    const div = document.querySelector('#errors');
    if (div) div.classList.remove('hidden');
  }

  function hideError() {
    const div = document.querySelector('#errors');
    if (div) div.classList.add('hidden');
  }

  function buildResultsHTML(images, url) {
    return images.map( image => `
      <div class="file-grid__file">
        <div class="file-thumbnail__wrapper">
          <a href="${base_file}/${image.file_id}">
          <div class="file-thumbnail">
            <img src="${image.url}" title="${image.title}" alt="${image.title}" class="" />
          </div>
          </a>
        </div>
      </div>`);
  }

  function getSizeValue() {
    return document.querySelector('input[name="size"]:checked').value;
  }

  function setError(title, desc) {
    const div = document.querySelector('#errors');
    const paragraphs = div.querySelectorAll('p');
    const divTitle = paragraphs[0];
    const divDesc = paragraphs[1];
    if (divTitle && title) divTitle.innerHTML = title;
    if (divDesc && desc) divDesc.innerHTML = desc;
  }

</script>