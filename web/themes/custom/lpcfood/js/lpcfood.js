(function ($, Drupal, drupalSettings, once) {
  Drupal.behaviors.lpcfood = {
    attach: function (context, settings) {
      $(document).ready(function() {
        $('.contact-message-feedback-form').addClass('contact-form card p-4 me-0 me-md-5');

        $(".lpcfood-news-feed-main > .view-content").addClass("col-lg-7 col-12 slick-news-main");
        $(".lpcfood-news-feed-main > .attachment-after").addClass("d-none d-lg-block col-lg-5 col-12 slick-news");
        $(".lpcfood-news-feed-main > .view-header").addClass("col-12");
        $(".lpcfood-news-feed-main").removeClass("d-none");
        $("#block-block-news").removeClass("d-none");

        // Get current path
        let path = window.location.pathname.split('/');
        let url = '';

        // Inspect each segment of the URL.
        for (let i = 1; i < path.length; i++) {
          url += '/' + path[i];

          // Locate matching links and set the class only for the first element
          $('a[href="' + url + '"]').eq(0).addClass('active' + i);
        }
      });
    }
  }

  Drupal.behaviors.loading = {
    attach: function (context, settings) {
      once('loading-page', '#layout-loading', context).forEach(function () {
        let offset = $("#layout-loading").offset();
        $("#layout-loading").css("height", '100vh');
        $(".main-loading").css({
          "height": "100vh",
          "overflow": "hidden"
        });
        $(".hidden-loading").hide();
        window.addEventListener("load", (event) => {
          $("#layout-loading").addClass("hiddenLayoutLoad");
          $(".main-loading").removeAttr("style");
          $(".hidden-loading").show();
        });
      });
    }
  };
}(jQuery, Drupal, drupalSettings, once));


