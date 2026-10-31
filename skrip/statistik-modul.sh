#!/bin/sh
# Statistik per modul untuk docs/AI-BIJAN-MASTER.md: modul, baris PHP, fail ujian, kaedah ujian, laluan API.
# Guna: sh skrip/statistik-modul.sh > /tmp/modul.tsv && python3 skrip/jana-master-dokumen.py . <folder-pejabat> /tmp/modul.tsv
for m in $(ls app/Modules); do
  loc=$(find app/Modules/$m -name '*.php' -exec cat {} + 2>/dev/null | wc -l)
  fail=$(grep -rlw "$m" tests --include=*.php 2>/dev/null)
  t=$(printf '%s\n' "$fail" | grep -c . )
  tm=$( [ -n "$fail" ] && grep -hoE "function test_[a-z_0-9]+|#\[Test\]" $fail | wc -l || echo 0)
  r=$(grep -cE "Route::(get|post|put|patch|delete)" app/Modules/$m/routes/api.php 2>/dev/null); r=${r:-0}
  printf "%s\t%s\t%s\t%s\t%s\n" "$m" "$loc" "$t" "$tm" "$r"
done
