jQuery(document).ready(function ($) {
  $("#hide_nav_check").change(function () {
    if (this.checked != true) {
      this.value="no";
    } else {
        this.value="yes";
    }
  });
});
