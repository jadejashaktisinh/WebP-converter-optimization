import React, { useState } from 'react';
import { createRoot } from 'react-dom/client';
import ImageConverter from './components/ImageConverter';
import BulkConverter from './components/BulkConverter';
import Settings from './components/Settings';

declare const webpOptData: {
    ajaxUrl: string;
    nonce: string;
};

const AdminMenu = () => {
    const [activeTab, setActiveTab] = useState('image-converter');

    return (
        <div className="webp-optimizer-container">
            <h1>WebP Optimizer Settings</h1>
            
            <nav className="nav-tab-wrapper">
                <a 
                    className={`nav-tab ${activeTab === 'image-converter' ? 'nav-tab-active' : ''}`}
                    onClick={() => setActiveTab('image-converter')}
                >
                    Image Converter
                </a>
                <a 
                    className={`nav-tab ${activeTab === 'bulk-converter' ? 'nav-tab-active' : ''}`}
                    onClick={() => setActiveTab('bulk-converter')}
                >
                    Bulk Converter
                </a>
                <a 
                    className={`nav-tab ${activeTab === 'active-fallback' ? 'nav-tab-active' : ''}`}
                    onClick={() => setActiveTab('active-fallback')}
                >
                    Active Fallback
                </a>
                <a 
                    className={`nav-tab ${activeTab === 'settings' ? 'nav-tab-active' : ''}`}
                    onClick={() => setActiveTab('settings')}
                >
                    Settings
                </a>
            </nav>

            <div className="tab-content">
                {activeTab === 'image-converter' && (
                    <ImageConverter 
                        ajaxUrl={webpOptData.ajaxUrl} 
                        nonce={webpOptData.nonce} 
                    />
                )}
                
                {activeTab === 'bulk-converter' && (
                    <BulkConverter 
                        ajaxUrl={webpOptData.ajaxUrl} 
                        nonce={webpOptData.nonce} 
                    />
                )}
                
                {activeTab === 'active-fallback' && (
                    <div className="tab-pane">
                        <h2>Active Fallback</h2>
                        <p>Configure fallback options for unsupported browsers.</p>
                    </div>
                )}
                
                {activeTab === 'settings' && (
                    <Settings 
                        ajaxUrl={webpOptData.ajaxUrl} 
                        nonce={webpOptData.nonce} 
                    />
                )}
            </div>
        </div>
    );
};

const mountPoint = document.getElementById('webp-optimizer-admin-root');

if (mountPoint) {
    const root = createRoot(mountPoint);
    root.render(<AdminMenu />);
} else {
    console.error('Mount point #webp-optimizer-admin-root not found!');
}
