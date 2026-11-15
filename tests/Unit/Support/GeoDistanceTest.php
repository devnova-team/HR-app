<?php

namespace Tests\Unit\Support;

use App\Support\GeoDistance;
use PHPUnit\Framework\TestCase;

class GeoDistanceTest extends TestCase
{
    public function test_same_point_is_zero_meters(): void
    {
        $d = GeoDistance::calculate(30.0444, 31.2357, 30.0444, 31.2357);

        $this->assertEqualsWithDelta(0.0, $d, 0.001);
    }

    public function test_one_degree_of_latitude_is_about_111_km(): void
    {
        $d = GeoDistance::calculate(0, 0, 1, 0);

        $this->assertEqualsWithDelta(111194.9, $d, 5.0);
    }

    public function test_cairo_to_alexandria_is_about_180_km(): void
    {
        $d = GeoDistance::calculate(30.0444, 31.2357, 31.2001, 29.9187);

        $this->assertEqualsWithDelta(180000, $d, 3000);
    }

    public function test_distance_is_symmetric(): void
    {
        $ab = GeoDistance::calculate(30.0444, 31.2357, 31.2001, 29.9187);
        $ba = GeoDistance::calculate(31.2001, 29.9187, 30.0444, 31.2357);

        $this->assertEqualsWithDelta($ab, $ba, 0.001);
    }

    public function test_point_inside_radius(): void
    {
        // 0.001 degree of latitude is about 111 meters.
        $this->assertTrue(GeoDistance::isWithinRadius(30.000, 31.0, 30.001, 31.0, 150));
    }

    public function test_point_outside_radius(): void
    {
        $this->assertFalse(GeoDistance::isWithinRadius(30.000, 31.0, 30.001, 31.0, 100));
    }

    public function test_exactly_on_the_radius_counts_as_inside(): void
    {
        $distance = GeoDistance::calculate(30.000, 31.0, 30.001, 31.0);

        $this->assertTrue(GeoDistance::isWithinRadius(30.000, 31.0, 30.001, 31.0, $distance));
    }
}