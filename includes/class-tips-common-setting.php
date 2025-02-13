<?php

class TipsRestSettings {
    function __construct() {
        $this->init();
    }

    public function init() {
        add_action('admin_menu', array($this, 'add_tips_rest_api_menu'));
        add_action('admin_enqueue_scripts', array($this, 'add_tips_rest_api_styles'));
        add_action('admin_enqueue_scripts', array($this, 'add_tips_rest_api_js'));

    }
    
    public function add_tips_rest_api_styles($hook_suffix) {
        if ('toplevel_page_tips-rest-api' === $hook_suffix) {
            wp_enqueue_style('tips-rest-api-css', plugin_dir_url(__FILE__) . '../assets/css/tips-rest-api.css');
        }
    }
        public function add_tips_rest_api_js($hook_suffix) {
        if ('toplevel_page_tips-rest-api' === $hook_suffix) {
            wp_enqueue_script('tips-find-verse-script', plugin_dir_url(__FILE__) . '../assets/js/tips-rest-api.js');
        }
    }
    public function add_tips_rest_api_menu() {
        // Add the main menu.
        add_menu_page(
            __('TIPs Rest APIs', 'tips-rest-api'),    
            __('TIPs Rest APIs', 'tips-rest-api'),    
            'manage_options',                              
            'tips-rest-api',                             
            array($this, 'tips_rest_setting_page'),       
            'dashicons-admin-tools',                       
            25                                             
        );
    }
    public function tips_rest_setting_page() {
        ?>
        <div class="wrap" id="Tips_Api">
           <h1><?php _e('TIPs Rest APIs', 'tips-rest-api'); ?></h1>
            <div class="accordion">
                <div class="box_sec">
                    <button class="accordion-button"><span class="method">GET</span> <span class="api_url">https://tips.translation.bible/wp-json/v1/bible/{books}</span></button>
                    <div class="accordion-content">
                         <h2 class="api_title">Verse List API</h2>
                          <div class="parameters">
                             <h3 class="para_title">Parameters</h3>
                             <span class="para">books </span><span class="para_req">*</span> <span class="para_des">Display All Book name and ID</span>
                         </div>
                         <span class="api_res_title">API Successful response</span>
                         <p class="api_response">
                             [
                              {
                                "books": {
                                  "OT": [
                                    {
                                      "id": "1",
                                      "abbr": "Gen",
                                      "name": "Genesis",
                                      "testament": "OT"
                                    },
                                    {
                                      "id": "2",
                                      "abbr": "Exod",
                                      "name": "Exodus",
                                      "testament": "OT"
                                    },
                                  ]
                                }
                              }
                            ]
                         </p>
                    </div>
                </div>
                <div class="box_sec">
                    <button class="accordion-button"><span class="method">GET</span> <span class="api_url">https://tips.translation.bible/wp-json/v1/bible/chapternumbers?bookId={gen} </span></button>
                    <div class="accordion-content">
                         <h2 class="api_title">Book Chapter API:</h2>
                         <div class="parameters">
                             <h3 class="para_title">Parameters</h3>
                             <span class="para">bookId </span><span class="para_req">*</span> <span class="para_des">Id of Book For Example - gen</span>
                         </div>
                         <span class="api_res_title">API Successful response</span>
                         <p class="api_response">
                            {
                              "chapter_numbers": {
                                "0": 1,
                                "32": 2,
                                "57": 3,
                                "81": 4,
                                "107": 5,
                                "139": 6,
                                "161": 7,
                                "185": 8,
                                "207": 9,
                                "236": 10,
                                "268": 11,
                              }
                            }
                         </p>
                    </div>
                </div>
                <div class="box_sec">
                    <button class="accordion-button"><span class="method">GET</span> <span class="api_url">https://tips.translation.bible/wp-json/v1/bible/chapterverses?bookId={gen}&chapterId={chapterno} </span></button>
                    <div class="accordion-content">
                         <h2 class="api_title">Verse Chapter API:</h2>
                         <div class="parameters">
                             <h3 class="para_title">Parameters</h3>
                             <span class="para">bookId </span><span class="para_req">*</span> <span class="para_des">Id of Book For Example - gen</span> <br>
                             <span class="para">chapterId </span><span class="para_req">*</span> <span class="para_des">chapter number of Book For Example - 1 or 2 </span>
                         </div>
                         <span class="api_res_title">API Successful response</span>
                         <p class="api_response">
                           {
                              "chapters": {
                                "Gen 1:0 (introduction)": "/tip_verse/?verseId=gen-10-introduction/",
                                "Gen 1:1": "/tip_verse/?verseId=gen-11/",
                                "Gen 1:2": "/tip_verse/?verseId=gen-12/",
                                "Gen 1:3": "/tip_verse/?verseId=gen-13/",
                                "Gen 1:4": "/tip_verse/?verseId=gen-14/",
                                }
                            }
                         </p>
                    </div>
                </div>
                <div class="box_sec">
                    <button class="accordion-button"><span class="method">GET</span> <span class="api_url">https://tips.translation.bible/wp-json/v1/bible/tip_verse?verseId={verseId} </span></button>
                    <div class="accordion-content">
                         <h2 class="api_title">Verse Related Story API:</h2>
                         <span class="api_res_title">API Successful response</span>
                         <div class="parameters">
                             <h3 class="para_title">Parameters</h3>
                             <span class="para">verseId </span><span class="para_req">*</span> <span class="para_des">Id of verse For Example - gen-11</span>
                         </div>
                         <p class="api_response">
                           {
                              "0": {
                                "id": "16004",
                                "title": {
                                  "rendered": "In the beginning",
                                  "hover_title": "Ἐν ἀρχῇ, בְּרֵאשִׁ֖ית",
                                  "title_link": "https://tips.translation.bible/detail/?in-the-beginning"
                                },
                                "slug": "in-the-beginning",
                                "geographical_link": null,
                                "link": "https://tips.translation.bible/story/in-the-beginning/",
                                "content": { Verse content }
                              }
                            }
                         </p>
                    </div>
                </div>
                <div class="box_sec">
                    <button class="accordion-button"><span class="method">GET</span> <span class="api_url">https://tips.translation.bible/wp-json/v1/bible/story/?storyId={story_slug} </span></button>
                    <div class="accordion-content">
                         <h2 class="api_title">Story Detail API:</h2>
                          <div class="parameters">
                             <h3 class="para_title">Parameters</h3>
                             <span class="para">story_slug </span><span class="para_req">*</span> <span class="para_des">Slug of specific story For Example - in-the-beginning </span>
                         </div>
                         <span class="api_res_title">API Successful response</span>
                         <p class="api_response">
                           [
                              {
                                "id": 16004,
                                "title": {
                                  "rendered": "In the beginning",
                                  "hover_title": "Ἐν ἀρχῇ, בְּרֵאשִׁ֖ית"
                                },
                                "geographical_link": null,
                                "date": "2021-01-07 19:38:36",
                                "slug": "in-the-beginning",
                                "link": "https://tips.translation.bible/story/in-the-beginning/",
                                "content": {
                                  "rendered": "\u003Cp\
                                }
                              }
                            ]
                         </p>
                    </div>
                </div>
                <div class="box_sec">
                    <button class="accordion-button"><span class="method">GET</span> <span class="api_url">https://tips.translation.bible/wp-json/v1/bible/tip_source/?sourceId={source_slug} </span></button>
                    <div class="accordion-content">
                         <h2 class="api_title">Source Related Story API:</h2>
                         <div class="parameters">
                             <h3 class="para_title">Parameters</h3>
                             <span class="para">sourceId </span><span class="para_req">*</span> <span class="para_des">Slug of source For Example - david-frank-sil</span>
                         </div>
                         <span class="api_res_title">API Successful response</span>
                         <p class="api_response">
                            {
                              "sourceData": {
                                "id": "30850",
                                "title": "David Frank (SIL)",
                                "slug": "david-frank-sil"
                              },
                              "storyData": [
                                {
                                  "id": "20209",
                                  "geographical_link": null,
                                  "title": {
                                    "rendered": "abuse, contempt, ridicule, scorn",
                                    "hover_title": "λοιδορούμενος, ἐμπαίζω, ἐκμυκτηρίζω, εἰς οὐθὲν λογισθῆναι",
                                    "title_link": "https://tips.translation.bible/detail/?contempt-scorn-ridicule-abuse"
                                  },
                                  "link": "https://tips.translation.bible/story/contempt-scorn-ridicule-abuse/",
                                  "content": {
                                    "rendered": "story content"
                                  },
                                  "translation_details": null
                                },
                              }
                            }
                         </p>
                    </div>
                </div>
                <div class="box_sec">
                    <button class="accordion-button"><span class="method">GET</span> <span class="api_url">https://tips.translation.bible/wp-json/v1/bible/tree-view?termId={termId} </span></button>
                    <div class="accordion-content">
                         <h2 class="api_title">Tree Data API:</h2>
                         <div class="parameters">
                             <h3 class="para_title">Parameters</h3>
                             <span class="para">termId </span><span class="para_req">*</span> <span class="para_des">Slug of specific story For Example - in-the-beginning </span>
                         </div>
                         <span class="api_res_title">API Successful response</span>
                         <p class="api_response">
                            [
                              {
                                "name": "salvation",
                                "original": "σωτηρία, יְשׁוּעָה, salvus"
                              },
                              {
                                "name": "receive help for bad deeds",
                                "language": "",
                                "parent": 0
                              },
                              {
                                "name": "help as to his soul",
                                "language": "",
                                "parent": 0
                              },
                            ]
                         </p>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}


new TipsRestSettings();


