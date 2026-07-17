document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-find-schools]').forEach((section) => {
    const form = section.querySelector('[data-find-schools-form]');
    const submitBtn = section.querySelector('[data-find-schools-submit]');
    const results = section.querySelector('[data-find-schools-results]');
    if (!form || !submitBtn || !results || typeof window.sfFindSchools === 'undefined') {
      return;
    }

    // The button starts disabled in markup so the form can never fall
    // back to a plain submission (which would put the address in a URL).
    submitBtn.disabled = false;

    form.addEventListener('submit', (event) => {
      event.preventDefault();
      const street = form.querySelector('[name="street"]').value.trim();
      const zip = form.querySelector('[name="zip"]').value.trim();
      const hp = form.querySelector('[name="sf_hp_website"]').value;

      if (!street) {
        renderError(results, 'Please enter a street address.');
        return;
      }

      submitBtn.disabled = true;
      submitBtn.textContent = 'Looking up...';
      results.innerHTML = '';

      fetch(sfFindSchools.restUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-WP-Nonce': sfFindSchools.nonce,
        },
        body: JSON.stringify({ street, zip, sf_hp_website: hp }),
      })
        .then((response) => response.json().then((data) => ({ ok: response.ok, data })))
        .then(({ ok, data }) => {
          if (!ok || data.error) {
            renderError(results, (data && data.message) || 'Something went wrong. Please try again.', data && data.official_tool_url);
            return;
          }
          renderResults(results, data);
        })
        .catch(() => {
          renderError(results, "We couldn't reach the lookup service. Please try again in a moment.");
        })
        .finally(() => {
          submitBtn.disabled = false;
          submitBtn.textContent = 'Find my schools';
        });
    });
  });

  function renderError(container, message, officialUrl) {
    const link = officialUrl
      ? ` <a href="${escapeAttr(officialUrl)}" target="_blank" rel="noopener">Open the official Forsyth County Schools lookup tool</a>.`
      : '';
    container.innerHTML = `<div class="notice-box"><p>${escapeHtml(message)}${link}</p></div>`;
  }

  function renderResults(container, data) {
    const levels = [
      ['Elementary', data.elementary],
      ['Middle', data.middle],
      ['High', data.high],
    ];

    let html = `<div class="notice-box"><p>Matched address: <strong>${escapeHtml(data.matched_address)}</strong></p></div>`;
    html += '<div class="card-grid find-my-schools__cards">';

    levels.forEach(([label, school]) => {
      if (!school) {
        html += `<article class="card"><div class="card__body"><p class="eyebrow">${escapeHtml(label)}</p><p>Not available.</p></div></article>`;
        return;
      }

      html += '<article class="card"><div class="card__body">';
      html += `<p class="eyebrow">${escapeHtml(label)}</p>`;
      html += `<h3>${escapeHtml(school.name)}</h3>`;
      if (school.grades) {
        html += `<p class="card-location">Grades ${escapeHtml(school.grades)}</p>`;
      }
      if (school.address) {
        html += `<p class="card-location">${escapeHtml(school.address)}</p>`;
      }
      html += '<div class="card__links">';
      if (school.profile_url) {
        html += `<a class="text-link" href="${escapeAttr(school.profile_url)}">View school profile</a>`;
      }
      if (school.official_url) {
        html += `<a class="text-link" href="${escapeAttr(school.official_url)}" target="_blank" rel="noopener">Official school page</a>`;
      }
      html += '</div></div></article>';
    });

    html += '</div>';
    html += `<p class="find-my-schools__source">Source: ${escapeHtml(data.source)} &middot; ${escapeHtml(data.boundary_vintage)}. Attendance boundaries can change -- Forsyth County Schools is the final authority.</p>`;

    container.innerHTML = html;
  }

  function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = String(str == null ? '' : str);
    return div.innerHTML;
  }

  function escapeAttr(str) {
    return escapeHtml(str).replace(/"/g, '&quot;');
  }
});
