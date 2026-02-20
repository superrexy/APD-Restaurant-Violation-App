"""HLS Playlist and Segment Management Module"""

import os
import glob


class HLSManager:
    """HLS playlist and segment management"""
    
    def __init__(self, output_dir: str, keep_count: int):
        """Initialize HLS manager
        
        Args:
            output_dir: Directory containing HLS files
            keep_count: Number of segments to keep
        """
        self.output_dir = output_dir
        self.keep_count = keep_count
    
    def is_playlist_valid(self, filepath: str) -> bool:
        """Check if m3u8 playlist is valid
        
        Args:
            filepath: Path to m3u8 file
        
        Returns:
            True if valid, False otherwise
        """
        try:
            with open(filepath, 'r') as f:
                content = f.read()
                return '#EXTM3U' in content and '.ts' in content
        except:
            return False
    
    def cleanup_old_segments(self) -> int:
        """Delete old segment files
        
        Returns:
            Number of files deleted
        """
        try:
            ts_files = glob.glob(f"{self.output_dir}/stream*.ts")
            ts_files.sort(key=lambda x: int(x.split('stream')[-1].split('.ts')[0]))
            
            if len(ts_files) > self.keep_count:
                to_delete = ts_files[:-self.keep_count]
                for file in to_delete:
                    os.remove(file)
                    print(f"[Cleanup] Deleted {file}")
                return len(to_delete)
            return 0
        except Exception as e:
            print(f"[Cleanup] Error: {e}")
            return 0
