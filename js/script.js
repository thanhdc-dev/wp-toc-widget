const headings = document.querySelectorAll(
  "h1.wp-block-heading, h2.wp-block-heading, h3.wp-block-heading, h4.wp-block-heading, h5.wp-block-heading, h6.wp-block-heading"
);
const sidebarLinks = document.querySelectorAll(".widget-toc__item a");

window.addEventListener("scroll", function () {
  headings.forEach(function (heading) {
    const currLink = document.querySelector(
      '.widget-toc__item a[href="#' + heading.id + '"]'
    );
    const currHeadingPos = heading.getBoundingClientRect().top;
    if (currHeadingPos <= 350) {
      sidebarLinks.forEach(function (sidebarLink) {
        if (sidebarLink.classList.contains("active")) {
          sidebarLink.classList.remove("active");
        }
      });

      currLink.classList.add("active");
    }
  });
});
