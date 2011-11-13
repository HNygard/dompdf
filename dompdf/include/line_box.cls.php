<?php
/**
 * @package dompdf
 * @link    http://www.dompdf.com/
 * @author  Fabien M�nager <fabien.menager@gmail.com>
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 * @version $Id$
 */

/**
 * The line box class
 *
 * This class represents a line box
 * http://www.w3.org/TR/CSS2/visuren.html#line-box
 *
 * @access protected
 * @package dompdf
 */
class Line_Box {

  /**
   * @var Block_Frame_Decorator
   */
  protected $_block_frame;

  /**
   * @var array
   */
  protected $_frames = array();
  
  /**
   * @var integer
   */
  public $wc = 0;
  
  /**
   * @var float
   */
  public $y = null;
  
  /**
   * @var float
   */
  public $w = 0.0;
  
  /**
   * @var float
   */
  public $h = 0.0;
  
  /**
   * @var float
   */
  public $left = 0.0;
  
  /**
   * @var float
   */
  public $right = 0.0;
  
  /**
   * @var Frame
   */
  public $tallest_frame = null;
  
  public $floating_blocks = array();
  
  /**
   * @var bool
   */
  public $br = false;
  
  /**
   * Class constructor
   *
   * @param Block_Frame_Decorator $frale the Block_Frame_Decorator containing this line
   */
  function __construct(Block_Frame_Decorator $frame, $y = 0) {
    $this->_block_frame = $frame;
    $this->_frames = array();
    $this->y = $y;
    
    $this->get_float_offsets();
  }
  
  function get_float_offsets() {
    static $anti_infinite_loop;
    
    $reflower = $this->_block_frame->get_reflower();
    
    if ( !$reflower ) return;
    
    $cb_w = null;
    
    if ( DOMPDF_ENABLE_CSS_FLOAT ) {
      $block = $this->_block_frame;
      $root = $block->get_root();
      $floating_frames = $root->get_floating_frames();
      
      foreach ( $floating_frames as $child_key => $floating_frame ) {
        $id = $floating_frame->get_id();
        
        if ( isset($this->floating_blocks[$id]) ) continue;
        
        $float = $floating_frame->get_style()->float;
        
        $floating_width = $floating_frame->get_margin_width();
        
        if (!$cb_w) {
          $cb_w = $floating_frame->get_containing_block("w");
        }
        
        $line_w = $this->get_width();
        
        if (!$floating_frame->_float_next_line && ($cb_w <= $line_w + $floating_width) && ($cb_w > $line_w) ) {
          $floating_frame->_float_next_line = true;
          continue;
        }
        
        // If the child is still shifted by the floating element
        if ( $anti_infinite_loop++ < 1000 &&
             $floating_frame->get_position("y") + $floating_frame->get_margin_height() > $this->y && 
             $block->get_position("x") + $block->get_margin_width() > $floating_frame->get_position("x")
             ) {
          if ( $float === "left" )
            $this->left  += $floating_width;
          else
            $this->right += $floating_width;
            
          $this->floating_blocks[$id] = true;
        }
        
        // else, the floating element won't shift anymore
        else {
          $root->remove_floating_frame($child_key);
        }
      }
    }
  }
  
  function get_width(){
    return $this->left + $this->w + $this->right;
  }

  /**
   * @return Block_Frame_Decorator
   */
  function get_block_frame() { return $this->_block_frame; }

  /**
   * @return array
   */
  function &get_frames() { return $this->_frames; }
  
  function add_frame(Frame $frame) {
    $this->_frames[] = $frame;
  }
  
  function __toString(){
    $props = array("wc", "y", "w", "h", "left", "right", "br");
    $s = "";
    foreach($props as $prop) {
      $s .= "$prop: ".$this->$prop."\n";
    }
    $s .= count($this->_frames)." frames\n";
    return $s;
  }
  /*function __get($prop) {
    if (!isset($this->{"_$prop"})) return;
    return $this->{"_$prop"};
  }*/
}

/*
class LineBoxList implements Iterator {
  private $_p = 0;
  private $_lines = array();
  
}
*/
