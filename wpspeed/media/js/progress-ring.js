class ProgressRing extends HTMLElement {
  constructor() {
    super();
    const stroke = this.getAttribute('stroke');
    const radius = this.getAttribute('radius');
    const normalizedRadius = radius - stroke * 2;
    this._circumference = normalizedRadius * 2 * Math.PI;

    this._root = this.attachShadow({mode: 'open'});
    this._root.innerHTML = `
      <svg
        height="${radius * 2}"
        width="${radius * 2}"
       >
         <circle
           stroke-dasharray="${this._circumference} ${this._circumference}"
           style="stroke-dashoffset:${this._circumference}"
           stroke-width="${stroke}"
           r="${normalizedRadius}"
           cx="${radius}"
           cy="${radius}"
        />
      </svg>
      <div class="progressidentifier"></div>

      <style>
        circle {
          transition: stroke-dashoffset 0.35s;
          transform: rotate(-90deg);
          transform-origin: 50% 50%;
        }
      </style>
    `;
  }
  
  setProgress(percent, identifier) {
    const offset = this._circumference - (percent / 100 * this._circumference);
    const circle = this._root.querySelector('circle');
    circle.style.strokeDashoffset = offset;
    if(parseInt(percent)) {
    	this._root.querySelector('.progressidentifier').innerHTML = percent;
    	if(percent >= 0 && percent <= 49) {
    		this._root.querySelector('.progressidentifier').style.color = '#CC0000'
    	}
    	if(percent >= 50 && percent <= 89) {
    		this._root.querySelector('.progressidentifier').style.color = '#C33300'
    	}
    	if(percent >= 90) {
    		this._root.querySelector('.progressidentifier').style.color = '#008000'
    	}
    	
    	switch(percent.length) {
    		case 1:
    			var leftOffset = '50px';
    			break;
    		
    		case 2:
    			var leftOffset = '40px';
        		break;
        		
    		case 3:
    			var leftOffset = '29px';
        		break;
    	}
    	
    	this._root.querySelector('.progressidentifier').style.position = 'absolute';
    	this._root.querySelector('.progressidentifier').style.top = '-70px';
    	this._root.querySelector('.progressidentifier').style.left = leftOffset;
    	this._root.querySelector('.progressidentifier').style.fontSize = '36px';
    }
  }
  
  setColor(color) {
	  let innerCircleTag = this._root.querySelector('circle');
	  innerCircleTag.setAttribute('stroke', color);
  }
  
  setBgColor(backgroundColor) {
	  let innerCircleTag = this._root.querySelector('circle');
	  innerCircleTag.setAttribute('fill', backgroundColor);
  }
  
  static get observedAttributes() {
    return ['progress', 'color', 'bgcolor'];
  }

  attributeChangedCallback(name, oldValue, newValue) {
    if (name === 'progress') {
      this.setProgress(newValue);
    }
    if (name === 'color') {
      this.setColor(newValue);
    }
    if (name === 'bgcolor') {
        this.setBgColor(newValue);
      }
  }
}

window.customElements.define('progress-ring', ProgressRing);