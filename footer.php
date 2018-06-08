    <footer class="footer section section--fullwidth">
      <div class="row">
        <p>Except where otherwise noted, content on this site is licensed under the <a href="https://creativecommons.org/licenses/by-sa/3.0/" rel="license external">Creative Commons Attribution Share-Alike License v3.0</a>
          or any later version.</p>
      </div>
    </footer>
  <!-- </div> -->
  <?php wp_footer(); ?>
  <script>
    // External links should open in a new tab.
    (function () {
      var postLinks = document.querySelectorAll('#content-main a');

      var origin = location.origin;

      for (var i = 0; i < postLinks.length; i++) {
        var link = postLinks[i];
        if (link.origin !== origin && !link.getAttribute('target')) {
          link.setAttribute('target', '_blank');
        }
      }
    })();

    window.addEventListener('load', function () {
      if (document.querySelector('#newsletterForm')) {
        var script = document.createElement('script');
        var path = document.head.getAttribute('data-template-path');
        script.setAttribute('src', path + '/js/newsletter.js');
        document.head.appendChild(script);
      }
    });
  </script>
</body>
</html>
