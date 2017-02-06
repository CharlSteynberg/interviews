<?php


#  func :: is_assoc_array : check if array is associative
#  --------------------------------------------------------------------------------------------------------------------------------------------
    function is_assoc_array($arr)
    {
        if (!is_array($arr) || empty($arr))
        { return false; }

        return (count(array_filter(array_keys($arr), 'is_string')) > 0);
    }
#  --------------------------------------------------------------------------------------------------------------------------------------------




#  func :: array_improv_proto : improvise for missing keys in given associative array with prototype - for
#  --------------------------------------------------------------------------------------------------------------------------------------------
    function array_improv_proto($givn,$prot)
    {
        if (is_assoc_array($prot) && !is_assoc_array($givn))
        { return $prot; }

        if (count(array_intersect_key(array_flip($prot),$item)) === count($prot))   # all prototype-keys exist in given
        { return $givn; }

        foreach ($prot as $pkey => $pval)   # improvise for each prototype-key missing in `$givn`
        {
            if (!array_key_exists($pkey,$givn))
            { $givn[$pkey] = $pval; continue; }

            if (is_array($pval))
            { $givn[$pkey] = array_improv_proto($givn[$pkey],$pval); }
        }

        return $givn;
    }
#  --------------------------------------------------------------------------------------------------------------------------------------------


?>
