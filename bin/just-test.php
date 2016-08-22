#!/usr/bin/env php
<?php

foreach (array_slice($argv, 1) as $file) {
    require_once $file;
}
