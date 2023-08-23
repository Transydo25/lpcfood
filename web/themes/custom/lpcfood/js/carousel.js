/**
 * @file
 * Belgrade Theme JS.
 */
(function ($, Drupal) {
  'use strict';
  function carouselBootrap(context){
    /*
    $('.menu-carousel .facets-widget-links .carousel-category').removeClass('carousel slide');
    $('.menu-carousel .facets-widget-links .carousel-category .facet-inactive').removeClass('carousel-inner');
    $('.menu-carousel .facets-widget-links .carousel-category .facet-inactive .facet-item').removeClass('carousel-item');
     */
    $( ".carousel" ).attr('id','productCarousel').append(
      `<button class="carousel-control-prev" type="button" data-bs-target="#productCarousel" data-bs-slide="prev">
    <span class = "carousel-control-prev-icon" aria-hidden="true"> </span>
    <span class = "visually-hidden"> Previous </span>
  </button>
  <button class="carousel-control-next" type="button" data-bs-target="#productCarousel" data-bs-slide="next">
    <span class="carousel-control-next-icon" aria-hidden="true"></span>
    <span class="visually-hidden"> Next </span>
  </button>`);
    $('.carousel .carousel-item').first().addClass('active');
    $('.menu-carousel .facets-widget-links .carousel-category .facet-inactive .facet-item').removeClass('carousel-item');

    let productCarousel = document.querySelector('#productCarousel');
    let carousel = new bootstrap.Carousel(productCarousel);

    let items = document.querySelectorAll('.carousel .carousel-item')
    items.forEach((el) => {
      const minPerSlide = 4
      let next = el.nextElementSibling
      for (let i = 1; i < minPerSlide; i++) {
        if (!next) {
          // wrap carousel by using first child
          next = items[0]
        }
        let cloneChild = next.cloneNode(true)
        el.appendChild(cloneChild.children[0])
        next = next.nextElementSibling
      }
    });
  }

  Drupal.behaviors.bootstrap_carousel = {
    attach: function (context) {
      $(context).find(".carousel").once("bootstrap-carousel").each(function () {
        carouselBootrap(context);
      });
    }
  };

})(jQuery, Drupal);
