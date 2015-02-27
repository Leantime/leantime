    package charts.series.bars {
       import flash.filters.DropShadowFilter;
       import flash.geom.Matrix;
       
       public class PlasticFlat extends Base
       {
          
          public function PlasticFlat( index:Number, props:Properties, group:Number ) {
             
             super(index, props, group);
             //super(index, {'top':props.get('top')}, props.get_colour('colour'), props.get('tip'), props.get('alpha'), group);
             
             var dropShadow:DropShadowFilter = new flash.filters.DropShadowFilter();
             dropShadow.blurX = 5;
             dropShadow.blurY = 5;
             dropShadow.distance = 3;
             dropShadow.angle = 45;
             dropShadow.quality = 2;
             dropShadow.alpha = 0.4;
             // apply shadow filter
             this.filters = [dropShadow];
          }
          
          public override function resize( sc:ScreenCoordsBase ):void {
             
             this.graphics.clear();
             var h:Object = this.resize_helper( sc as ScreenCoords );
             
             this.bg( h.width, h.height, h.upside_down );
             this.glass( h.width, h.height, h.upside_down );
          }
          
          private function bg( w:Number, h:Number, upside_down:Boolean ):void {

             var rad:Number = w/3;
             if ( rad > ( w / 2 ) )
                rad = w / 2;
                
             this.graphics.lineStyle(0, 0, 0);// this.outline_colour, 100);
             
             var allcolors:Array = GetColours(this.colour);
             var lowlight:Number = allcolors[2];
             var highlight:Number = allcolors[0];
             var bgcolors:Array = [allcolors[1], allcolors[2], allcolors[2]];
             var bgalphas:Array = [1, 1, 1];
             var bgratios:Array = [0, 115, 255];
             //var bgcolors:Array = [allcolors[1], allcolors[2]];
             //var bgalphas:Array = [1, 1];
             //var bgratios:Array = [0, 255];
             var bgmatrix:Matrix = new Matrix();
             var xRadius:Number;
             var yRadius:Number;
             var x:Number;
             var y:Number;
             var bevel:Number = 0.02 * w;
             var div:Number = 3;
             
             bgmatrix.createGradientBox(w, h, (180 / 180) * Math.PI );
             this.graphics.beginGradientFill('linear' /*GradientType.Linear*/, bgcolors, bgalphas, bgratios, bgmatrix, 'pad'/*SpreadMethod.PAD*/ );
             
                if ( h > 0 || h < 0)
                { /* height is not zero */
                   
                   /* draw outline darker rounded rectangle */
                   this.graphics.beginFill(0x000000, 1);         
                   this.graphics.drawRoundRect(0, 0, w, h, w/div, w/div);
                   
                   /* draw inner highlight rounded rectangle */
                   this.graphics.beginFill(highlight, 1);
                   this.graphics.drawRoundRect(0 + bevel, 0 + bevel, w - 2 * bevel, h - 2 * bevel, w/div - 2*bevel, w/div - 2 * bevel);
                   
                   /* draw inner gradient rounded rectangle */
                   bevel = bevel * 3;
                   this.graphics.beginGradientFill('linear' /*GradientType.Linear*/, bgcolors, bgalphas, bgratios, bgmatrix, 'pad'/*SpreadMethod.PAD*/ );
                   this.graphics.drawRoundRect(0 + bevel, 0 + bevel, w - 2 * bevel, h - 2 * bevel, w/div - 2*bevel, w/div - 2 * bevel);
                   
                }
                else
                {
                   
                   /* draw outline darker rounded rectangle */
                   this.graphics.beginFill(0x000000, 1);         
                   this.graphics.drawRoundRect(0, 0 - 2*bevel, w, h + 4*bevel, w/div, w/div);
                   
                   /* draw inner highlight rounded rectangle */
                   this.graphics.beginFill(highlight, 1);
                   this.graphics.drawRoundRect(0 + bevel, 0 - 2*bevel + bevel, w - 2 * bevel, h + 4*bevel - 2 * bevel, w/div - 2*bevel, w/div - 2 * bevel);
                   
                   /* draw inner gradient rounded rectangle */
                   bevel = bevel * 3;
                   this.graphics.beginGradientFill('linear' /*GradientType.Linear*/, bgcolors, bgalphas, bgratios, bgmatrix, 'pad'/*SpreadMethod.PAD*/ );
                   this.graphics.drawRoundRect(0 + bevel, 0 - 2*bevel + bevel, w - 2 * bevel, h + 4*bevel - 2 * bevel, w/div - 2*bevel, w/div - 2 * bevel);
                   
                }
                
                
             this.graphics.endFill();
          }
          
          private function glass( w:Number, h:Number, upside_down:Boolean ): void {
             
             /* if this section is commented out, the white shine overlay will not be drawn */
             
             this.graphics.lineStyle(0, 0, 0);
             var allcolors:Array = GetColours(this.colour);
             var lowlight:Number = allcolors[2];
             var highlight:Number = allcolors[0];
             var bgcolors:Array = [allcolors[1], allcolors[2], allcolors[2]];
             var bgalphas:Array = [1, 1, 1];
             var bgratios:Array = [0, 115, 255];
             var bgmatrix:Matrix = new Matrix();
             /*var colors:Array = [0xFFFFFF, 0xFFFFFF];
             var alphas:Array = [0, 0.75];
             var ratios:Array = [127,255];   */
             var colors:Array = [0xFFFFFF, 0xFFFFFF, 0xFFFFFF];
             var alphas:Array = [0, 0.05, 0.75];
             var ratios:Array = [0, 123, 255];         
             var xRadius:Number;
             var yRadius:Number;
             var x:Number;
             var y:Number;
             var matrix:Matrix = new Matrix();
             var bevel:Number = 0.02 * w;
             var div:Number = 3;
             
             bgmatrix.createGradientBox(w, h, (180 / 180) * Math.PI );
             matrix.createGradientBox(width, height, (180 / 180) * Math.PI );
             this.graphics.beginGradientFill('linear' /*GradientType.Linear*/, colors, alphas, ratios, matrix, 'pad'/*SpreadMethod.PAD*/ );
             var rad:Number = w / 3;
                
                if ( h > 0 && !upside_down )
                { /* draw bar upwards */
                
                   /* draw shine rounded rectangle */      
                   this.graphics.drawRoundRect(0 + bevel, 0 + bevel, w - 2 * bevel, h - 2 * bevel, w/div - 2*bevel, w/div - 2 * bevel);
                   
                   /* draw outline darker rectangle */
                   this.graphics.beginFill(0x000000, 1);         
                   this.graphics.drawRect(0, h - h / 2, w, h/2);
                   
                   /* draw inner highlight rectangle */
                   this.graphics.beginFill(highlight, 1);
                   this.graphics.drawRect(0 + bevel, h - h / 2, w - 2 * bevel, h /2 - bevel);
                   
                   /* draw inner gradient rectangle */
                   bevel = bevel * 3;
                   this.graphics.beginGradientFill('linear' /*GradientType.Linear*/, bgcolors, bgalphas, bgratios, bgmatrix, 'pad'/*SpreadMethod.PAD*/ );
                   this.graphics.drawRect(0 + bevel, h - h / 2, w - 2 * bevel, h / 2 - bevel);
                   
                   /* draw shine rounded rectangle */      
                   bevel = bevel / 3;
                   this.graphics.beginGradientFill('linear' /*GradientType.Linear*/, colors, alphas, ratios, matrix, 'pad'/*SpreadMethod.PAD*/ );
                   this.graphics.drawRect(0 + bevel, h - h / 2, w - 2 * bevel, h / 2 - bevel);
                   
                }
                else if ( h > 0 )
                {/* draw bar downwards */
                
                   /* draw shine rounded rectangle */      
                   this.graphics.drawRoundRect(0 + bevel, 0 + bevel, w - 2 * bevel, h - 2 * bevel, w/div - 2*bevel, w/div - 2 * bevel);
                   
                   /* draw outline darker rectangle */
                   this.graphics.beginFill(0x000000, 1);         
                   this.graphics.drawRect(0, 0, w, h/2);
                   
                   /* draw inner highlight rectangle */
                   this.graphics.beginFill(highlight, 1);
                   this.graphics.drawRect(0 + bevel, 0 + bevel, w - 2 * bevel, h /2 - bevel);
                   
                   /* draw inner gradient rectangle */
                   bevel = bevel * 3;
                   this.graphics.beginGradientFill('linear' /*GradientType.Linear*/, bgcolors, bgalphas, bgratios, bgmatrix, 'pad'/*SpreadMethod.PAD*/ );
                   this.graphics.drawRect(0 + bevel, 0 + bevel, w - 2 * bevel, h / 2 - bevel);
                   
                   /* draw shine rounded rectangle */      
                   bevel = bevel / 3;
                   this.graphics.beginGradientFill('linear' /*GradientType.Linear*/, colors, alphas, ratios, matrix, 'pad'/*SpreadMethod.PAD*/ );
                   this.graphics.drawRect(0 + bevel, 0 + bevel, w - 2 * bevel, h / 2 - bevel);
                   
                }
                else
                {
                   
                   /* draw shine rounded rectangle */      
                   this.graphics.drawRoundRect(0 + bevel, 0 - 2*bevel + bevel, w - 2 * bevel, h + 4*bevel - 2 * bevel, w/div - 2*bevel, w/div - 2 * bevel);
                   
                }
                   this.graphics.endFill();
                
          }
          
          /* function to process colors */
          /* returns a base color, a lowlight color, and a highlight color for the gradients based on the color passed in */
          public static function GetColours( col:Number):Array {
             var rgb:Number = col; /* decimal value for color */
             var red:Number = (rgb & 16711680) >> 16; /* extacts the red channel */
             var green:Number = (rgb & 65280) >> 8; /* extacts the green channel */
             var blue:Number = rgb & 255; /* extacts the blue channel */
             var shift:Number = 0.15; /* shift factor */
             var loshift:Number = 1.75; /* lowlight shift factor */
             var basecolor:Number = col; /* base color to be returned */
             var lowlight:Number = col; /* lowlight color to be returned */
             var highlight:Number = col; /* highlight color to be returned */
             var bgred:Number = (rgb & 16711680) >> 16; /* red channel for highlight */
             var bggreen:Number = (rgb & 65280) >> 8; /* green channel for highlight */
             var bgblue:Number = rgb & 255; /* blue channel for highlight */
             var lored:Number = (rgb & 16711680) >> 16; /* red channel for lowlight */
             var logreen:Number = (rgb & 65280) >> 8; /* green channel for lowlight */
             var loblue:Number = rgb & 255; /* blue channel for lowlight */
             var hired:Number = (rgb & 16711680) >> 16; /* red channel for highlight */
             var higreen:Number = (rgb & 65280) >> 8; /* green channel for highlight */
             var hiblue:Number = rgb & 255; /* blue channel for highlight */
             
             /* set base color components based on ability to shift lighter and darker */   
             if (red + red * shift < 255 && red - loshift * red * shift > 0)
             { /* red can be shifted both lighter and darker */
                bgred = red;
             }
             else
             { /* red can be shifter either lighter or darker */
                if (red + red * shift < 255)
                { /* red can be shifter lighter */
                   bgred = red + red / shift;
                }
                else
                { /* red can be shifted darker */
                   bgred = red - loshift * red * shift;
                }
             }
                
             if (blue + blue * shift < 255 && blue - loshift * blue * shift > 0)
             { /* blue can be shifted both lighter and darker */
                bgblue = blue;
             }
             else
             { /* blue can be shifter either lighter or darker */
                if (blue + blue * shift < 255)
                { /* blue can be shifter lighter */
                   bgblue = blue + blue * shift;
                }
                else
                { /* blue can be shifted darker */
                   bgblue = blue - loshift * blue * shift;
                }
             }
                
             if (green + green * shift < 255 && green - loshift * green * shift > 0)
             { /* green can be shifted both lighter and darker */
                bggreen = green;
             }
             else
             { /* green can be shifted either lighter or darker */
                if (green + green * shift < 255)
                { /* green can be shifter lighter */
                   bggreen = green + green * shift;
                }
                else
                { /* green can be shifted darker */
                   bggreen = green - loshift * green * shift;
                }
             }
             
             /* set highlight and lowlight components based on base colors */   
             hired = bgred + red * shift;
             lored = bgred - loshift * (red * shift);
             hiblue = bgblue + blue * shift;
             loblue = bgblue - loshift * (blue * shift);
             higreen = bggreen + green * shift;
             logreen = bggreen - loshift * (green * shift);
             
             /* reconstruct base and highlight */
             basecolor = bgred << 16 | bggreen << 8 | bgblue;
             highlight = hired << 16 | higreen << 8 | hiblue;
             lowlight = lored << 16 | logreen << 8 | loblue;
                      
             /* return base, lowlight, and highlight */
             return [highlight, basecolor, lowlight];
          }
          
          /* ellipse cos helper function */
          public static function magicTrigFunctionX (pointRatio:Number):Number{
             return Math.cos(pointRatio*2*Math.PI);
          }
          
          /* ellipse sin helper function */
          public static function magicTrigFunctionY (pointRatio:Number):Number{
             return Math.sin(pointRatio*2*Math.PI);
          }
          
          /* ellipse function */
          /* draws an ellipse from passed center coordinates, x and y radii, and number of sides */
          public function Ellipse(centerX:Number, centerY:Number, xRadius:Number, yRadius:Number, sides:Number):Number{
             
             /* move to first point on ellipse */
             this.graphics.moveTo(centerX + xRadius,  centerY);
             
             /* loop through sides and draw curves */
             for(var i:Number=0; i<=sides; i++){
                var pointRatio:Number = i/sides;
                var xSteps:Number = magicTrigFunctionX(pointRatio);
                var ySteps:Number = magicTrigFunctionY(pointRatio);
                var pointX:Number = centerX + xSteps * xRadius;
                var pointY:Number = centerY + ySteps * yRadius;
                this.graphics.lineTo(pointX, pointY);
             }
             
             /* return 1 */
             return 1;
          }
                
       }
    }