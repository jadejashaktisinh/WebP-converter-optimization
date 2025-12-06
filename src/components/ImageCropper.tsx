import React, { useState, useCallback } from 'react';
import Cropper from 'react-easy-crop';

interface ImageCropperProps {
    imageSrc: string;
    onSave: (croppedAreaPixels: any) => void;
    onCancel: () => void;
}

const ImageCropper: React.FC<ImageCropperProps> = ({ imageSrc, onSave, onCancel }) => {
    const [crop, setCrop] = useState({ x: 0, y: 0 });
    const [zoom, setZoom] = useState(1);
    const [croppedAreaPixels, setCroppedAreaPixels] = useState(null);

    const onCropComplete = useCallback((croppedArea: any, croppedAreaPixels: any) => {
        setCroppedAreaPixels(croppedAreaPixels);
    }, []);

    const handleSave = () => {
        if (croppedAreaPixels) {
            onSave(croppedAreaPixels);
        }
    };

    return (
        <div className="crop-modal-overlay">
            <div className="crop-modal">
                <div className="crop-container">
                    <Cropper
                        image={imageSrc}
                        crop={crop}
                        zoom={zoom}
                        aspect={4 / 3}
                        onCropChange={setCrop}
                        onZoomChange={setZoom}
                        onCropComplete={onCropComplete}
                    />
                </div>
                <div className="crop-controls">
                    <label>
                        Zoom:
                        <input
                            type="range"
                            min={1}
                            max={3}
                            step={0.1}
                            value={zoom}
                            onChange={(e) => setZoom(Number(e.target.value))}
                        />
                    </label>
                    <div className="crop-buttons">
                        <button onClick={handleSave} className="button button-primary">
                            Save Crop
                        </button>
                        <button onClick={onCancel} className="button">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default ImageCropper;
