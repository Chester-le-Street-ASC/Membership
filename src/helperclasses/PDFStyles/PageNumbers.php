<script type="text/php">
  if ( isset($pdf) ) {
    $x = 36;
    $y = 813;
    $text = "{PAGE_NUM}"; //  of {PAGE_COUNT}
    $font = $fontMetrics->get_font("Open Sans Bold", "normal");
    $size = 10;
    $color = array(0,0,0);
    $word_space = 0.0;  //  default
    $char_space = 0.0;  //  default
    $angle = 0.0;   //  default
    $pdf->page_text($x, $y, $text, $font, $size, $color, $word_space, $char_space, $angle);
  }
</script>