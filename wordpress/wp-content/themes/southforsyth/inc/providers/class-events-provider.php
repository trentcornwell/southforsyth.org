<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * ICS (iCalendar) event feed provider — a small, dependency-free VEVENT
 * parser, exactly as scoped in docs/data-integration-roadmap.md's
 * "Calendar/ICS sources" section ("not a Composer package, to keep the
 * 'no plugins' / lightweight constraint"). Targets church, school, county
 * parks, library, and Chamber of Commerce calendars per that document.
 *
 * Recurrence (RRULE) is NOT expanded here — the roadmap document flags that
 * as the hardest part to get right and recommends expanding into individual
 * event posts for a bounded window (e.g. 90 days) at import time, not at
 * parse time. A VEVENT with an RRULE is returned as a single occurrence
 * with `recurring => true` so Southforsyth_Importer can decide what to do
 * with it, rather than silently dropping or mis-expanding it here.
 */
class Southforsyth_Events_Provider extends Southforsyth_Abstract_Provider
{
    public function __construct()
    {
        parent::__construct('events_ics');
    }

    /** $query is an ICS feed URL; "search" here means "list its events". */
    public function search($query, array $args = array())
    {
        return $this->cache('ics_' . $query, HOUR_IN_SECONDS, function () use ($query) {
            $body = $this->http_get($query, array(), false);
            return $body ? $this->parse_ics($body) : array();
        });
    }

    public function fetch($id, array $args = array())
    {
        $events = $this->search($args['feed_url'] ?? '');
        foreach ($events as $event) {
            if (($event['uid'] ?? '') === $id) {
                return $event;
            }
        }
        return null;
    }

    public function normalize($raw)
    {
        if (empty($raw)) {
            return array();
        }

        return Southforsyth_Normalizer::shape(array(
            'source'    => $this->get_slug(),
            'source_id' => $raw['uid'] ?? '',
            'post_type' => 'event',
            'title'     => $raw['summary'] ?? '',
            'content'   => $raw['description'] ?? '',
            'meta'      => array(
                'sf_event_date'  => $raw['start_date'] ?? '',
                'sf_event_time'  => $raw['start_time'] ?? '',
                'sf_event_venue' => $raw['location'] ?? '',
            ),
            'recurring' => ! empty($raw['recurring']),
        ));
    }

    /** Parses VEVENT blocks out of raw ICS text into simple associative arrays. */
    private function parse_ics($text)
    {
        $text = str_replace(array("\r\n ", "\r\n\t"), '', $text); // unfold long lines
        $lines = preg_split('/\r\n|\n|\r/', $text);

        $events = array();
        $current = null;

        foreach ($lines as $line) {
            if ('BEGIN:VEVENT' === trim($line)) {
                $current = array();
                continue;
            }

            if ('END:VEVENT' === trim($line)) {
                if (null !== $current) {
                    $events[] = $this->map_vevent($current);
                }
                $current = null;
                continue;
            }

            if (null === $current || false === strpos($line, ':')) {
                continue;
            }

            list($key, $value) = explode(':', $line, 2);
            $key = strtoupper(explode(';', $key, 2)[0]); // strip params, e.g. DTSTART;TZID=...
            $current[$key] = $this->unescape_ics_value($value);
        }

        return $events;
    }

    private function map_vevent(array $props)
    {
        $start = $this->parse_ics_datetime($props['DTSTART'] ?? '');

        return array(
            'uid'         => $props['UID'] ?? md5(wp_json_encode($props)),
            'summary'     => $props['SUMMARY'] ?? '',
            'description' => $props['DESCRIPTION'] ?? '',
            'location'    => $props['LOCATION'] ?? '',
            'start_date'  => $start['date'] ?? '',
            'start_time'  => $start['time'] ?? '',
            'recurring'   => ! empty($props['RRULE']),
        );
    }

    private function parse_ics_datetime($value)
    {
        if (! $value) {
            return array('date' => '', 'time' => '');
        }

        // All-day: YYYYMMDD. Timed: YYYYMMDDTHHMMSS(Z).
        if (preg_match('/^(\d{4})(\d{2})(\d{2})(?:T(\d{2})(\d{2})(\d{2}))?/', $value, $m)) {
            $date = "{$m[1]}-{$m[2]}-{$m[3]}";
            $time = isset($m[4]) ? "{$m[4]}:{$m[5]}" : '';
            return array('date' => $date, 'time' => $time);
        }

        return array('date' => '', 'time' => '');
    }

    private function unescape_ics_value($value)
    {
        return str_replace(array('\\n', '\\N', '\\,', '\\;', '\\\\'), array("\n", "\n", ',', ';', '\\'), trim($value));
    }
}
