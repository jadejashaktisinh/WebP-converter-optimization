import React, { useState } from 'react';
import ImageCropper from './ImageCropper';

interface ImageFile {
    file: File;
    preview: string;
    cropped?: any;
}

interface ImageConverterProps {
    ajaxUrl: string;
    nonce: string;
}

const ImageConverter: React.FC<ImageConverterProps> = ({ ajaxUrl, nonce }) => {
    const [images, setImages] = useState<ImageFile[]>([]);
    const [loading, setLoading] = useState(false);
    const [message, setMessage] = useState('');
    const [results, setResults] = useState<any[]>([]);
    const [cropIndex, setCropIndex] = useState<number | null>(null);

    const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        if (e.target.files && e.target.files.length > 0) {
            const newImages: ImageFile[] = [];
            
            Array.from(e.target.files).forEach((file) => {
                const reader = new FileReader();
                reader.onload = () => {
                    newImages.push({
                        file,
                        preview: reader.result as string,
                    });
                    
                    if (newImages.length === e.target.files!.length) {
                        setImages(newImages);
                    }
                };
                reader.readAsDataURL(file);
            });
            
            setMessage('');
            setResults([]);
        }
    };

    const handleRemove = (index: number) => {
        setImages(images.filter((_, i) => i !== index));
    };

    const handleCropClick = (index: number) => {
        setCropIndex(index);
    };

    const handleCropSave = (pixels: any) => {
        if (cropIndex !== null) {
            const updatedImages = [...images];
            updatedImages[cropIndex].cropped = pixels;
            setImages(updatedImages);
            setCropIndex(null);
        }
    };

    const handleCropCancel = () => {
        setCropIndex(null);
    };

    const getCroppedImg = async (imageSrc: string, pixelCrop: any): Promise<Blob> => {
        const image = await createImage(imageSrc);
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');

        canvas.width = pixelCrop.width;
        canvas.height = pixelCrop.height;

        ctx?.drawImage(
            image,
            pixelCrop.x,
            pixelCrop.y,
            pixelCrop.width,
            pixelCrop.height,
            0,
            0,
            pixelCrop.width,
            pixelCrop.height
        );

        return new Promise((resolve) => {
            canvas.toBlob((blob) => {
                resolve(blob as Blob);
            }, 'image/jpeg');
        });
    };

    const createImage = (url: string): Promise<HTMLImageElement> => {
        return new Promise((resolve, reject) => {
            const image = new Image();
            image.addEventListener('load', () => resolve(image));
            image.addEventListener('error', (error) => reject(error));
            image.src = url;
        });
    };

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        
        if (images.length === 0) {
            setMessage('Please select images to convert');
            return;
        }

        setLoading(true);
        setMessage('');

        const formData = new FormData();
        formData.append('action', 'convert_images');
        formData.append('nonce', nonce);

        for (const img of images) {
            if (img.cropped) {
                const croppedBlob = await getCroppedImg(img.preview, img.cropped);
                formData.append('images[]', croppedBlob, img.file.name);
            } else {
                formData.append('images[]', img.file);
            }
        }

        try {
            const response = await fetch(ajaxUrl, {
                method: 'POST',
                body: formData,
            });

            const data = await response.json();

            if (data.success) {
                setMessage(data.data.message);
                setResults(data.data.converted);
                setImages([]);
                (document.getElementById('image-upload') as HTMLInputElement).value = '';
            } else {
                setMessage(data.data.message || 'Conversion failed');
            }
        } catch (error) {
            setMessage('Error: ' + (error as Error).message);
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="image-converter">
            <h2>Image Converter</h2>
            <form onSubmit={handleSubmit}>
                <div className="form-group">
                    <label htmlFor="image-upload">Select Images:</label>
                    <input
                        type="file"
                        id="image-upload"
                        accept="image/*"
                        multiple
                        onChange={handleFileChange}
                        disabled={loading}
                    />
                </div>

                {images.length > 0 && (
                    <div className="images-preview-grid">
                        {images.map((img, index) => (
                            <div key={index} className="image-preview-item">
                                <img src={img.preview} alt={`Preview ${index + 1}`} />
                                <div className="preview-actions">
                                    <button 
                                        type="button" 
                                        onClick={() => handleCropClick(index)} 
                                        className="button button-small"
                                        disabled={loading}
                                    >
                                        Crop
                                    </button>
                                    <button 
                                        type="button" 
                                        onClick={() => handleRemove(index)} 
                                        className="button button-small"
                                        disabled={loading}
                                    >
                                        Remove
                                    </button>
                                </div>
                                {img.cropped && (
                                    <span className="crop-badge">✓ Cropped</span>
                                )}
                            </div>
                        ))}
                    </div>
                )}

                <button type="submit" className="button button-primary" disabled={loading || images.length === 0}>
                    {loading ? 'Converting...' : `Convert ${images.length} Image(s) to WebP`}
                </button>
            </form>

            {cropIndex !== null && (
                <ImageCropper
                    imageSrc={images[cropIndex].preview}
                    onSave={handleCropSave}
                    onCancel={handleCropCancel}
                />
            )}

            {message && (
                <div className={`notice notice-${results.length > 0 ? 'success' : 'error'}`}>
                    <p>{message}</p>
                </div>
            )}

            {results.length > 0 && (
                <div className="converted-images">
                    <h3>Converted Images:</h3>
                    <ul>
                        {results.map((result, index) => (
                            <li key={index}>
                                <a href={result.url} target="_blank" rel="noopener noreferrer">
                                    View Image (ID: {result.id})
                                </a>
                            </li>
                        ))}
                    </ul>
                </div>
            )}
        </div>
    );
};

export default ImageConverter;
