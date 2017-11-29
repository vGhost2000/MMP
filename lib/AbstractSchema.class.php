<?php

abstract class AbstractSchema
{
    public function load(Mysqli $db)
    {
        $drop = [];
        $other = [];

        foreach ($this->buildQueries() as $query) {
            $query = trim($query);
            if (preg_match('#^drop#i', $query)) {
                $drop[] = $query;
            } else {
                $other[] = $query;
            }
        }
        $drop = array_reverse($drop);
        foreach ($drop as $query) {
            Output::verbose($query);
            if (!$db->query($query)) {
                throw new Exception("Fail\n{$query}\n{$db->error}");
            }
        }
        foreach ($other as $query) {
            Output::verbose($query);
            if (!$db->query($query)) {
                throw new Exception("Fail\n{$query}\n{$db->error}");
            }
        }
    }

    protected function buildQueries()
    {
        return isset($this->queries) ? $this->queries : array();
    }
}