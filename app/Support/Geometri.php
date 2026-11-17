<?php

namespace App\Support;

class Geometri
{
    /**
     * Convex hull dari daftar titik [x, y]
     * Dalam kasus ini [longitude, latitude].
     *
     * Hasil berurutan berlawanan arah jarum jam,
     * tanpa titik penutup.
     */
    public static function convexHull(array $titik): array
    {
        $titik = array_values(array_unique($titik, SORT_REGULAR));

        usort($titik, fn ($a, $b) => [$a[0], $a[1]] <=> [$b[0], $b[1]]);

        if (count($titik) < 3) {
            return $titik;
        }

        // Menghitung arah belokan antar titik
        $silang = fn ($o, $a, $b) =>
            ($a[0] - $o[0]) * ($b[1] - $o[1])
            - ($a[1] - $o[1]) * ($b[0] - $o[0]);

        // Membentuk bagian bawah hull
        $bawah = [];

        foreach ($titik as $p) {
            while (
                count($bawah) >= 2 &&
                $silang(
                    $bawah[count($bawah) - 2],
                    $bawah[count($bawah) - 1],
                    $p
                ) <= 0
            ) {
                array_pop($bawah);
            }

            $bawah[] = $p;
        }

        // Membentuk bagian atas hull
        $atas = [];

        foreach (array_reverse($titik) as $p) {
            while (
                count($atas) >= 2 &&
                $silang(
                    $atas[count($atas) - 2],
                    $atas[count($atas) - 1],
                    $p
                ) <= 0
            ) {
                array_pop($atas);
            }

            $atas[] = $p;
        }

        array_pop($bawah);
        array_pop($atas);

        return array_merge($bawah, $atas);
    }
}
