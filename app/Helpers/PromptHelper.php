<?php

//generate rating 
function generateRatings($rating = 0) {
    $ratingtxt = ''; 
    
    
    for ($i = 1; $i <= 5; $i++) {
        $ratingtxt .= ($i <= $rating) ? 
                      '<i class="bi bi-star-fill text-warning" style="font-size: 13px;"></i>' : 
                      '<i class="bi bi-star text-secondary" style="color: #d1d5db !important; font-size: 13px;"></i>';
    }
    
    return $ratingtxt; 
}



// Available AI Tools
function availableTools(): array
{
    return [
        'chatgpt'    => 'https://chatgpt.com',
        'claude'     => 'https://claude.ai',
        'gemini'     => 'https://gemini.google.com',
        'midjourney' => 'https://www.midjourney.com',
        'dalle'      => 'https://openai.com/index/dall-e-3',
        'perplexity' => 'https://www.perplexity.ai',
        'deepseek'   => 'https://www.deepseek.com',
        'canva'      => 'https://www.canva.com',
        'copy_ai'    => 'https://www.copy.ai',
        'jasper'     => 'https://www.jasper.ai',
    ];
}