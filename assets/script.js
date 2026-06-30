document.addEventListener('DOMContentLoaded', function () {

  const container = document.getElementById('instagram-feed');
  const { userId, accessToken } = ifsData;

  if (!userId || !accessToken) {
    container.innerHTML = '<p>Please configure your Instagram Feed settings in WP Admin.</p>';
    return;
  }

  const url = `https://graph.facebook.com/v19.0/${userId}/media?fields=id,caption,media_url,timestamp&access_token=${accessToken}`;

  fetch(url)
    .then(res => res.json())
    .then(data => {
      if (!data || !Array.isArray(data.data)) {
        const error = data.error?.message || 'Invalid response from Instagram API.';
        container.innerHTML = `<p>Error: ${error}</p>`;
        return;
      }

      data.data.forEach(post => {
        const div = document.createElement('div');
        div.className = 'insta-post';
        div.innerHTML = `
          <img src="${post.media_url}" alt="Instagram post" />
          <p>${post.caption || ''}</p>
          <small>${new Date(post.timestamp).toLocaleDateString()}</small>
        `;
        container.appendChild(div);
      });
    })
    .catch(err => {
      console.error('Instagram fetch failed:', err);
      container.innerHTML = '<p>Network error while loading Instagram feed.</p>';
    });
});
