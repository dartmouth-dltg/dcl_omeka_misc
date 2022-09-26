<?php
  namespace DclOmekaMisc;

  return array(
    // override the default thumbnail config
    'thumbnails' => [
      'types' => [
          'large' => ['constraint' => 2100],
          'medium' => ['constraint' => 800],
          'square' => ['constraint' => 300],
          /*
              Custom Thumbnail Types
              - set strategy to 'custom'
              - set x and y constraints as array
              - set crop (optional)
              - example:
                  'og_image' => [
                      'strategy' => 'custom',
                      'constraint' => [
                          'constraint_x' => 1200, 
                          'constraint_y' => 600
                      ],
                      'options' => [
                          'gravity' => 'center', 
                          'crop' => true
                      ]
                  ] 
          */
          '16x9' => [
              'strategy' => 'custom',
              'constraint' => [
                  'constraint_x' => 1440, 
                  'constraint_y' => 810
              ],
              'options' => [
                  'gravity' => 'center', 
                  'crop' => true
              ]
          ],
          'og_image' => [
              'strategy' => 'custom',
              'constraint' => [
                  'constraint_x' => 1200, 
                  'constraint_y' => 630
              ],
              'options' => [
                  'gravity' => 'center', 
                  'crop' => true
              ]
          ]
      ],
      'thumbnailer_options' => [
          'imagemagick_dir' => null,
      ],
    ]
  );
  