#!/usr/bin/env python3
# facedetect python script

from __future__ import division, generators, print_function, unicode_literals

import argparse
import math
import os
import sys
from typing import Dict

import cv2
import numpy as np
from PIL import Image
# try to import pillow_avif to handle AVIF images
try:
    import pillow_avif
except ImportError:
    pass

# Constants
DATA_DIR = '/usr/share/opencv4/'
NORM_SIZE = 100
NORM_MARGIN = 10
SEARCH_THRESHOLD = 30
PROFILES = {
    'HAAR_FRONTALFACE_ALT2': 'haarcascades/haarcascade_frontalface_alt2.xml',
}

# Face detection class
class FaceDetect:
    def __init__(self, data_dir: str = DATA_DIR):
        self.data_dir = data_dir
        self.cascades: Dict[str, cv2.CascadeClassifier] = {}
        self.load_cascades()

    def load_cascades(self) -> None:
        for k, v in PROFILES.items():
            v = os.path.join(self.data_dir, v)
            if not os.path.exists(v):
                self.error_exit(f"cannot load {k} from {v}")
            self.cascades[k] = cv2.CascadeClassifier(v)

    def get_features(self, im: np.ndarray) -> list:
        side = math.sqrt(im.size)
        minlen = int(side / 20)
        maxlen = int(side / 2)
        flags = cv2.CASCADE_DO_CANNY_PRUNING

        cascade = self.cascades['HAAR_FRONTALFACE_ALT2']
        return cascade.detectMultiScale(im, 1.1, 1, flags, (minlen, minlen), (maxlen, maxlen))

    def facedetect_file(self, path: str) -> tuple:
        # convert avif to jpg
        if path.endswith('.avif'):
            img = Image.open(path)
            img.save(path + '-tmp.jpg')
            path = path + '-tmp.jpg'
        im = cv2.imread(path, cv2.IMREAD_GRAYSCALE)
        if im is None:
            self.error_exit(f"cannot load input image {path}")
        im = cv2.equalizeHist(im)
        features = self.get_features(im)
        if path.endswith('-tmp.jpg'):
            os.remove(path)
        return im, features

    def error_exit(self, msg: str) -> None:
        sys.stderr.write(f"{os.path.basename(sys.argv[0])}: error: {msg}\n")
        sys.exit(1)

    def run(self, file: str) -> int:
        _, non_sorted_features = self.facedetect_file(file)
        features = sorted(non_sorted_features, key=lambda x: x[0])
        for rect in features:
            print(f"{' '.join(map(str, rect))}")

        return 0

def __main__() -> int:
    ap = argparse.ArgumentParser(description='A simple facedetect python script')
    ap.add_argument('file', help='Input image file')
    args = ap.parse_args()

    face_detect = FaceDetect()
    return face_detect.run(args.file)

if __name__ == '__main__':
    sys.exit(__main__())
