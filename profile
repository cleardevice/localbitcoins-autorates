gt() {
  awk '{ if ($3 > '$1') print $0 }'
}

lt() {
  awk '{ if ($3 < '$1') print $0 }'
}
