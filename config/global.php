<?php

return [
    "keyword_match_weight"=>env("KEYWORD_MATCH_WEIGHT",0.3),
    "reviews_match_weight"=>env("REVIEWS_MATCH_WEIGHT",0.7),
    "score"=>env("SCORE",0.5),
    "overly_rated_weight"=>env("OVERLY_RATED_WEIGHT",0.1),
    "punctuation_weight"=>env("PUNCTAUTION_WEIGHT",0.1),
    "sentiment_weight"=>env("SENTIMENT_WEIGHT",0.1),
    "punctuation_threshold"=>env("PUNCTUATION_THRESHOLD",0.3),
];
